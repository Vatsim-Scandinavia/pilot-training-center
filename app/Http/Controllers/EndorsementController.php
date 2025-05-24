<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Endorsement;
use App\Models\InstructorEndorsement;
use App\Models\PilotRating;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EndorsementController extends Controller
{
    /**
     * Display a listing of the Solo endorsement
     *
     * @return \Illuminate\Http\Response
     */
    public function indexSolos()
    {
        $endorsements = Endorsement::where('type', 'SOLO')->with('positions', 'user')
            ->where(function ($q) {
                $q->orWhere(function ($q2) {
                    $q2->where('expired', false)
                        ->where('revoked', false);
                })
                    ->orWhere(function ($q2) {
                        $q2->where(function ($q3) {
                            $q3->where('valid_to', '>=', Carbon::now()->subDays(14));
                        })
                            ->where(function ($q3) {
                                $q3->where('expired', true)
                                    ->orWhere('revoked', true);
                            });
                    });
            })
            ->get();

        // Sort endorsements
        $endorsements = $endorsements->sortByDesc('valid_to');

        return view('endorsements.solos', compact('endorsements'));
    }

    /**
     * Display a listing of the users with examiner endorsements
     *
     * @return \Illuminate\Http\Response
     */
    public function indexExaminers()
    {
        $endorsements = Endorsement::where('type', 'EXAMINER')->where('revoked', false)->get();
        $areas = Area::all();

        return view('endorsements.examiners', compact('endorsements', 'areas'));
    }

    /**
     * Display a listing of the users with visiting endorsements
     *
     * @return \Illuminate\Http\Response
     */
    public function indexVisitors()
    {
        $endorsements = Endorsement::where('type', 'VISITING')->where('revoked', false)->with('user', 'ratings', 'areas.ratings')->get();
        $areas = Area::all();

        return view('endorsements.visiting', compact('endorsements', 'areas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($prefillUserId = null)
    {
        $this->authorize('create', Endorsement::class);
        if ($prefillUserId) {
            $users = User::with('instructorEndorsements')->where('id', $prefillUserId)->get();
        } else {
            $users = User::allWithGroup(4);
            $users->load('instructorEndorsements');
        }
        $ratings = PilotRating::whereIn('vatsim_rating', [1, 3, 7, 15, 63])->get();

        $userEndorsementsMap = $users->mapWithKeys(function ($user) {
            return [$user->id => $user->instructorEndorsements->pluck('pilot_rating_id')->toArray()];
        });

        return view('endorsements.create', compact('users', 'ratings', 'prefillUserId', 'userEndorsementsMap'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', [Endorsement::class]);

        $data = $request->validate([
            'user' => 'required|numeric|exists:users,id',
            'rating' => 'sometimes|array',
            'rating.*' => 'exists:pilot_ratings,id',
        ]);

        $user = User::findOrFail($data['user']);

        if (! $user->isInstructor()) {
            return redirect()->back()->withErrors('User is not an instructor');
        }

        // Get current rating endorsements
        $existing = $user->instructorEndorsements()->pluck('pilot_rating_id')->toArray();

        // New set of selected ratings from the form
        $new = $data['rating'] ?? [];

        // Determine ratings to add and remove
        $toAdd = array_diff($new, $existing);
        $toRemove = array_diff($existing, $new);

        // Add new endorsements
        foreach ($toAdd as $ratingId) {
            $rating = PilotRating::find($ratingId);
            self::createInstructorEndorsementModel($user, $rating);

            ActivityLogController::warning('ENDORSEMENT', 'Created instructor endorsement ' .
                ' ― User: ' . $user->id .
                ' ― Rating: ' . $rating->name);
        }

        // Remove unselected endorsements
        if (! empty($toRemove)) {
            InstructorEndorsement::where('user_id', $user->id)
                ->whereIn('pilot_rating_id', $toRemove)
                ->delete();

            foreach ($toRemove as $ratingId) {
                $rating = PilotRating::find($ratingId);
                ActivityLogController::warning('ENDORSEMENT', 'Removed instructor endorsement ' .
                    ' ― User: ' . $user->id .
                    ' ― Rating: ' . $rating->name);
            }
        }

        return redirect()->intended(route('roster'))->withSuccess("{$user->name}'s endorsements updated");
    }

    private static function createInstructorEndorsementModel(User $user, PilotRating $rating)
    {
        $endorsement = new InstructorEndorsement();
        $endorsement->user_id = $user->id;
        $endorsement->pilot_rating_id = $rating->id;
        $endorsement->issued_by = \Auth::user()->id;
        $endorsement->save();
    }
}
