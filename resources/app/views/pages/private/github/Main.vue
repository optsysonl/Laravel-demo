<template>
    <Page :title="page.title" :breadcrumbs="page.breadcrumbs" :actions="page.actions">

            <Alert class="mb-4"></Alert>
            <Form id="github-usersearch" @submit.prevent="onFormSubmit">
                <TextInput type="text" :label="trans('github.labels.searchquery')" name="searchquery" v-model="form.searchquery" autocomplete="email" class="mb-2"/>
                <div class="text-center">
                    <Button type="submit" :label="trans('global.buttons.search')"/>
                </div>
            </Form>

            <Table :id="page.id" v-if="table" :headers="table.headers" :sorting="table.sorting" :actions="table.actions" :records="table.records" :pagination="table.pagination" :is-loading="table.loading" @page-changed="onTablePageChange" @action="onTableAction" @sort="onTableSort">
                <template v-slot:content-id="props">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <img v-if="props.item.avatar_thumb_url" :src="props.item.avatar_thumb_url" class="h-10 w-10 rounded-full" alt=""/>
                            <Avatar v-else class="w-10 h-10 text-gray-400 rounded-full"/>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ props.item.full_name }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ trans('users.labels.id') + ': ' + props.item.id }}
                            </div>
                        </div>
                    </div>
                </template>
                <template v-slot:content-status="props">
                    <span v-if="props.item.email_verified_at" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800" v-html="trans('users.status.verified')"></span>
                    <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800" v-html="trans('users.status.not_verified')"></span>
                </template>
                <template v-slot:content-role="props">
                    {{
                        props.item.roles.map((entry) => {
                            return entry.title
                        }).join(', ')
                    }}
                </template>
            </Table>
    </Page>
</template>

<script>
import {trans} from "@/helpers/i18n"
import GithubService from "@/services/GithubService";
import Form from "@/views/components/Form";
import Alert from "@/views/components/Alert";
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
        Alert,
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
            searchquery: null,
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

        const table = reactive({
            headers: {
                avatar: trans('github.labels.avatar'),
                followers: trans('github.labels.followers'),
            },
            pagination: {
                meta: null,
                links: null,
            },
            loading: false,
            records: null
        })

        function fetchPage(params) {
            table.records = [];
            table.loading = true;
            let query = prepareGitHubQuery(params);
            service
                .index(query)
                .then((response) => {
                    table.records = response.data.data;
                    table.pagination.meta = response.data.meta;
                    table.pagination.links = response.data.links;
                    table.loading = false;
                })
                .catch((error) => {
                    alertStore.error(getResponseError(error));
                    table.loading = false;
                });
        }

        function onFormSubmit() {
            mainQuery.searchUsername = form.searchquery;
            fetchPage(mainQuery);
        }

        watch(mainQuery, (newTableState) => {
            fetchPage(mainQuery);
        });

        onMounted(() => {
            fetchPage(mainQuery);
        });

        return {
            trans,
            page,
            table,
            onFormSubmit,
            form,
            mainQuery
        }
    }
});
</script>
