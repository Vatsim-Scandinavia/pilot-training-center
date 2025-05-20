<?php

namespace App\Http\Controllers;

use anlutro\LaravelSettings\Facade as Setting;
use App;
use App\Models\PilotTrainingReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Controller for the dashboard
 */
class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        $report = PilotTrainingReport::whereIn('pilot_training_id', $user->pilotTrainings->pluck('id'))->orderBy('created_at')->get()->last();

        $subdivision = $user->subdivision;
        if (empty($subdivision)) {
            $subdivision = 'No subdivision';
        }

        $data = [
            'rating' => $user->rating_long,
            'rating_short' => $user->rating_short,
            'pilotrating' => $user->pilotrating_long,
            'pilotrating_short' => $user->pilotrating_short,
            'division' => $user->division,
            'subdivision' => $subdivision,
            'report' => $report,
        ];

        $trainings = $user->pilotTrainings;
        $statuses = PilotTrainingController::$statuses;

        // If the user belongs to our subdivision, doesn't have any training requests, has S2+ rating and is marked as inactive -> show notice
        $allowedSubDivisions = explode(',', Setting::get('trainingSubDivisions'));
        // $completedTrainingMessage = $user->hasRecentlyCompletedTraining();

        $workmailRenewal = (isset($user->setting_workmail_expire)) ? (Carbon::parse($user->setting_workmail_expire)->diffInDays(Carbon::now(), false) > -7) : false;

        // Cache the pilot hours for 1 hour
        $cachekey = 'pilot_hours_' . $user->id;

        $vatsimStats = Cache::remember($cachekey, 120, function() use ($user){
            $client = new \GuzzleHttp\Client();

            $url = App::environment('production') 
                ? 'https://api.vatsim.net/api/ratings/' . $user->id . '/rating_times/' 
                : 'https://api.vatsim.net/api/ratings/819096/rating_times/';

            try {
                $res = $client->request('GET', $url);
                if ($res->getStatusCode() === 200) {
                    return json_decode($res->getBody(), true);
                }
            } catch (\Exception $e) {
                return null;
            }
        });

        $pilotHours = $vatsimStats['pilot'] ?? null;

        $studentTrainings = \Auth::user()->instructingTrainings();

        $cronJobError = (($user->isAdmin() && App::environment('production')) && (\Carbon\Carbon::parse(Setting::get('_lastCronRun', '2000-01-01')) <= \Carbon\Carbon::now()->subMinutes(5)));

        $oudatedVersionWarning = $user->isAdmin() && Setting::get('_updateAvailable');

        $ptmCIDWarning = $user->isAdmin() && Setting::get('ptmCID') == null;
        $ptmMailWarning = $user->isAdmin() && Setting::get('ptmEmail') == null;

        return view('dashboard', compact('data', 'trainings', 'statuses', 'pilotHours', 'workmailRenewal', 'studentTrainings', 'cronJobError', 'oudatedVersionWarning', 'ptmCIDWarning', 'ptmMailWarning'));
    }

    /**
     * Show the training apply view
     *
     * @return \Illuminate\View\View
     */
    public function apply()
    {
        return view('trainingapply');
    }

    /**
     * Show member endorsements view
     *
     * @return \Illuminate\View\View
     */
    public function endorsements()
    {
        $members = User::has('ratings')->get()->sortBy('name');

        return view('endorsements', compact('members'));
    }
}
