<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Position;
use App\Models\Vote;
use App\Models\Voter;
use Illuminate\Support\Facades\DB;

class VoterController extends Controller
{
    public function googleLogin(Request $request)
    {
        $validated =  $request->validate([
            'email' => 'required|email',
            'pfNumber' => 'required|numeric'
        ]);

        $user = null;

        $chunks = Voter::chunk(100, function ($users) use ($validated, &$user) {
            $user = $users->first(function ($user) use ($validated) {
                return $user->email === $validated['email'] && $user->pfNumber === $validated['pfNumber'];
            });
            if ($user) {
                return;
            }
        });

        if (!$user) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }
        $token  = $user->createToken('bearer_token')->plainTextToken;

        return response()->json([
            'message' => 'Success',
            'status' => 1,
            'photo' => $user->picture_url,
            'name' => $user->name,
            'bearer_token' => $token,
            'balance' => $user->balance ?? 0
        ], 200);


    }

    public function vvpat()
    {
        $vvpat = Vote::with(['position', 'voter'])->get();

        return response()->json([
            'data' => [
                'votes' => $vvpat->map(function ($vote) {
                    return [
                        'id' => $vote->id,
                        'vvpat' => $vote->vvpat,
                        'count' => $vote->count,
                        'position' => $vote->position->name,
                        'name' => $vote->voter->name,
                    ];
                }),
            ]
        ]);
    }
    private function checkIfVoted($details): bool
    {

        Vote::chunk(100, function ($users) use ($details, &$existingUser) {
            $existingUser = $users->first(function ($user) use ($details) {
                if ($user->voter_id == $details['id']) {
                    return true; // does exist
                }

                return false; // does not exist
            });
        });


        if ($existingUser) {
            return true; // does  exist/////
        }

        return false; // does not exists
    }

    public function getCandidatesV2(Request $request)
    {

        if ($this->checkIfVoted(['id' => $request->user()->id])) {
            return response()->json([
                'issue' => 'You have already voted'
            ], 400);
        }

        $voters = Voter::has('position')->get();
        $positions = [];

        foreach ($voters as $key => $voter) {
            $positionName = $voter->position->name;
            $positionId = $voter->position->id;

            // Initializes the position array if it doesn't exist
            if (!isset($positions[$positionName])) {
                $positions[$positionName] = [
                    'position_id' => $positionId,
                    'title' => $positionName,
                    'values' => []
                ];
            }

            // Adds the voter to the corresponding position
            $positions[$positionName]['values'][] = [
                'id' => $voter->id,
                'photo' => $voter->picture_url,
                'name' => $voter->name
            ];
        }

        // Converts associative array to indexed array and add IDs
        $candidate = array_values(array_map(function ($position, $index) {
            return array_merge(['id' => $index], $position);
        }, $positions, array_keys($positions)));

        return response()->json([
            'data' => $candidate
        ]);
    }

    public function vote(Request $request)
    {
        try {
            if ($this->checkIfVoted(['id' => $request->user()->id])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already voted'
                ], 409);
            }
            // Validate the request
            $request->validate([
                'candidates' => 'required|array',
                'candidates.*' => 'required|exists:voters,id'
            ]);
            $votes = [];

            DB::beginTransaction();
            try {
                foreach ($request->candidates as $candidateId) {
                    $candidate = Voter::where('id', $candidateId)
                        ->whereNotNull('position_id')
                        ->firstOrFail();

                    $currentVoteCount = $candidate->votes()->exists() ? $candidate->votes()->max('count') : 0;

                    $vote = new Vote();

                    $vote->count = $currentVoteCount + 1;
                    $vote->user_id = $candidate->id;
                    $vote->voter_id = $request->user()->id;
                    $vote->position_id = $candidate->position_id;

                    $vote->save();
                }

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Votes casted successfully'
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage(),
                'message' => 'Failed to cast vote'
            ], 500);
        }
    }
    private function checkIfUserExists($details): bool
    {

        Voter::chunk(100, function ($users) use ($details, &$existingUser) {
            $existingUser = $users->first(function ($user) use ($details) {
                if ($user->email === $details['email'] || $user->pfNumber === $details['pfNumber'] || $user->phone === $details['phone']) {
                    return true; // does exist
                }

                return false; // does not exist
            });
        });


        if ($existingUser) {
            return true; // does not exist/////
        }

        return false; // does exists
    }
    public function signUp(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|max_digits:10',
            'pfNumber' => 'required|max:4',
            'email' => 'required|max:150',
            'email_verified' => 'required|boolean',
            'google_id' => 'required|numeric',
            'picture_url' => 'required|sometimes|url'
        ]);


        try {
            if ($this->checkIfUserExists([
                'phone' => $validated['phone'],
                'pfNumber' => $validated['pfNumber'],
                'email' => $validated['email']
            ])) {
                return response()->json([
                    'message' => "User already exists",
                    'issue' => "User already exists"
                ], 409);
            }

            $validated['ip_address'] = $request->ip();

            $google2fa = app('pragmarx.google2fa');
            //   $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());


            $validated['secret'] = $google2fa->generateSecretKey(); //secret

            $inlineUrl = $google2fa->getQRCodeInline(  //url
                'MyKura' . $validated['pfNumber'],
                'gwatainicholas@gmail.com',
                $validated['secret']
            );

            $validated['inline_url'] = $inlineUrl;

            //             print_r($validated);
            // die();
            $user = Voter::create($validated);

            return response()->json([
                'data' => $user,
                'message' => 'Success'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'issue' => 1
            ], 500);
        }
    }
    public function getResults()
    {
        try {
            $votes = Vote::with(['position', 'voter'])->get();

            $groupedVotes = $votes->groupBy('position.name');

            $formattedData = $groupedVotes->map(function ($votes, $positionName) {
                return [
                    'title' => $positionName,
                    'values' => $votes->groupBy('user_id')
                        ->map(function ($userVotes) {
                            return [
                                'name' => $userVotes->first()->voter->name,
                                'count' => $userVotes->max('count')
                            ];
                        })
                        ->values()->toArray(),
                    'total' => $votes->groupBy('user_id')
                        ->map(function ($userVotes) {
                            return $userVotes->max('count');
                        })
                        ->sum()];
            })->values();

            return response()->json([
                'status' => 'success',
                'message' => 'Results retrieved successfully',
                'data' => [
                    'data' => $formattedData

                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve positions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPositions()
    {
        try {
            $positions = Position::all();

            return response()->json([
                'status' => 'success',
                'message' => 'Positions retrieved successfully',
                'data' => $positions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve positions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
