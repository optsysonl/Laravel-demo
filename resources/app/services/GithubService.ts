import ModelService from "@/services/ModelService";

export default class GithubService extends ModelService {

    constructor() {
        super();
        this.url = '/github';
    }

}
