<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use GrahamCampbell\GitHub\Facades\GitHub;

class GithubController extends Controller
{
    /**
     * The service instance
     *
     * @var GithubService
     */
    protected $githubService;

    /**
     * Constructor
     */
    public function __construct(GitHub $service)
    {
        $this->githubService = $service;
    }

    /**
     * Handle search data
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     *
     * @throws AuthorizationException
     */
    public function search(Request $request)
    {
        $per_page = 20;
        $searchUsername = $request->searchUsername;
        $page = intval($request->page);
        $params = [
            'page' => $page,
            'per_page' => $per_page,
        ];

        $followers_count = GitHub::users()->show($searchUsername)['followers'];
        $followers = GitHub::users()->followers($searchUsername, $params);
        $total_pages = ceil($followers_count / $per_page);

        return [
            'github_handle'=> $searchUsername,
            'followers_count'=> $followers_count,
            'followers' => $followers,
            'pagination' => [
                'meta' => [
                    'current_page' => $page,
                    'from' => 1,
                    'last_page' => $total_pages,
                ],
                'links' => '',            ]
        ];
    }
}
