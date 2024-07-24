<template>
    <Page :title="page.title" :breadcrumbs="page.breadcrumbs" :actions="page.actions">
        gdarko
            <Form id="github-usersearch" @submit.prevent="onFormSearchSubmit">
                <TextInput type="text" :label="trans('github.labels.searchusername')" name="searchusername" v-model="form.searchusername" autocomplete="email" class="mb-2" />
                <div class="text-center">
                    <Button type="submit" :label="trans('global.buttons.search')"/>
                </div>
            </Form>

            <div>
                User: {{ github_user.github_handle }}
            </div>
            <div>
                Follower Count: {{ github_user.github_followers_count }}
            </div>

            <Table :id="page.id" v-if="github_user.followers_table" :headers="github_user.followers_table.headers" :records="github_user.followers_table.records" :pagination="github_user.followers_table.pagination" :is-loading="github_user.followers_table.loading">
                <template v-slot:content-avatar_url="props">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <img v-if="props.item.avatar_url" :src="props.item.avatar_url" class="h-10 w-10 rounded-full" alt=""/>
                            <Avatar v-else class="w-10 h-10 text-gray-400 rounded-full"/>
                        </div>
                    </div>
                </template>
            </Table>

            <template v-if="github_user.followers_table.is_load_more">
                <Form id="github-load_more" @submit.prevent="onFormLoadSubmit" >
                    <Button type="submit" :label="trans('global.buttons.load_more')" />
                </Form>
            </template>
    </Page>
</template>

<script>
import {trans} from "@/helpers/i18n"
import GithubService from "@/services/GithubService";
import Form from "@/views/components/Form";
import Button from "@/views/components/input/Button";
import TextInput from "@/views/components/input/TextInput";
import {defineComponent, onMounted, reactive, watch} from "vue";
import {useAlertStore} from "@/stores";
import {toUrl} from "@/helpers/routing";
import {getResponseError, prepareGitHubQuery} from "@/helpers/api";
import Dropdown from "@/views/components/input/Dropdown";
import Page from "@/views/layouts/Page";
import Table from "@/views/components/Table";
import Avatar from "@/views/components/icons/Avatar";

export default defineComponent({
    name: "GitHubMain",
    components: {
        Form,
        Button,
        Dropdown,
        TextInput,
        Page,
        Table,
        Avatar
    },
    emits: ['error'],

    setup(emits) {
        const service = new GithubService();
        const form = reactive({
            searchusername: '',
        })
        const alertStore = useAlertStore();
        const mainQuery = reactive({
            page: 1,
            searchUsername: '',
        });

        const page = reactive({
            id: 'github_usersearch',
            title: trans('global.pages.github_usersearch'),
            breadcrumbs: [
                {
                    name: trans('global.pages.github_usersearch'),
                    to: toUrl('/github'),
                    active: true,
                }
            ],
            actions: [
                {
                    id: 'searchUser',
                    name: trans('global.buttons.search'),
                    icon: "fa fa-filter",
                    theme: 'outline',
                },
            ],
        });

        const github_user = reactive({
            github_handle: null,
            github_followers_count: null,
            followers_table: {
                headers: {
                    avatar_url: trans('github.labels.avatar'),
                    login: trans('github.labels.followers'),
                },
                pagination: {
                    meta: null,
                    links: null,
                },
                actions: {},
                loading: false,
                records: null,
                is_load_more: false
            }
        })

        function fetchPage(params) {
            github_user.github_handle = '';
            github_user.github_followers_count = '';
            github_user.followers_table.records = [];
            github_user.followers_table.loading = true;
            alertStore.clear();
            let query = prepareGitHubQuery(params);
            service
                .index(query)
                .then((response) => {
                    github_user.github_handle = response.data.github_handle;
                    github_user.github_followers_count = response.data.followers_count;
                    github_user.followers_table.records = response.data.followers;
                    github_user.followers_table.pagination.meta = response.data.pagination.meta;
                    github_user.followers_table.pagination.links = response.data.pagination.links;
                    github_user.followers_table.loading = false;
                    if(response.data.pagination.meta.current_page === response.data.pagination.meta.last_page){
                        github_user.followers_table.is_load_more = false;
                    }else{
                        github_user.followers_table.is_load_more = true;
                    }
                })
                .catch((error) => {
                    alertStore.error(getResponseError(error));
                    github_user.followers_table.loading = false;
                });
        }




        function onFormSearchSubmit() {
            mainQuery.searchUsername = form.searchusername;
            fetchPage(mainQuery);
        }
        function onFormLoadSubmit(page) {
            mainQuery.searchUsername = form.searchusername;
            mainQuery.page = github_user.followers_table.pagination.meta.current_page;
            mainQuery.page++;
            fetchPage(mainQuery);
        }


        watch(mainQuery, (newTableState) => {
            fetchPage(mainQuery);
        });

        onMounted(() => {
            //fetchPage(mainQuery);
        });

        return {
            trans,
            page,
            github_user,
            onFormSearchSubmit,
            onFormLoadSubmit,
            form,
            mainQuery
        }
    }
});
</script>
