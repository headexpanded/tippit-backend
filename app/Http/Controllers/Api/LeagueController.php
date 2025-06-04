<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\League\StoreLeagueRequest;
use App\Http\Requests\League\UpdateLeagueRequest;
use App\Http\Resources\BasicLeagueResource;
use App\Models\League;
use App\Services\LeagueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class LeagueController extends Controller
{
    protected LeagueService $leagueService;

    /**
     * @param  LeagueService  $leagueService
     */
    public function __construct(LeagueService $leagueService)
    {
        $this->leagueService = $leagueService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return BasicLeagueResource::collection(League::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLeagueRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(League $league): BasicLeagueResource
    {
        return new BasicLeagueResource($league);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(League $leagues)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLeagueRequest $request, League $leagues)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(League $leagues)
    {
        //
    }

    /**
     * @param  League  $league
     *
     * @return JsonResponse
     */
    public function getRankings(League $league): JsonResponse
    {
        if (!$league->users()->where('users.id', Auth::id())->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $rankings = $this->leagueService->getRankings($league);
        return response()->json($rankings);
    }
}
