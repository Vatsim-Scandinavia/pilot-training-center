<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\PilotRating;
use App\Models\PilotTraining;
use App\Models\User;
use App\Notifications\ExamNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    public function createTheory($prefillUserId = null)
    {
        $this->authorize('create', Exam::class);

        // Only show trainings that are in have AWAITING_EXAM status
        if ($prefillUserId) {
            $users = User::where('id', $prefillUserId)->with(['pilotTrainings' => function ($query) {
                $query->where('status', 1);
            }, 'pilotTrainings.pilotRatings'])->get();
        } else {
            $users = User::with(['pilotTrainings' => function ($query) {
                $query->where('status', 1);
            }, 'pilotTrainings.pilotRatings'])->get();
        }

        $ratings = PilotRating::whereIn('vatsim_rating', [1, 3, 7, 15, 31])->get();

        return view('exam.create', compact('users', 'ratings', 'prefillUserId'));
    }

    public function createPractical($prefillUserId = null)
    {
        $this->authorize('create', Exam::class);

        // Only show trainings that are in have AWAITING_EXAM status
        if ($prefillUserId) {
            $users = User::where('id', $prefillUserId)->with(['pilotTrainings' => function ($query) {
                $query->where('status', 3);
            }, 'pilotTrainings.pilotRatings'])->get();
        } else {
            $users = User::with(['pilotTrainings' => function ($query) {
                $query->where('status', 3);
            }, 'pilotTrainings.pilotRatings'])->get();
        }

        $ratings = PilotRating::whereIn('vatsim_rating', [1, 3, 7, 15, 31])->get();

        return view('exam.practical.create', compact('users', 'ratings', 'prefillUserId'));
    }

    public function storeTheory(Request $request)
    {
        $this->authorize('store', [Exam::class]);

        $data = [];
        $data = request()->validate([
            'user' => 'required|numeric|exists:App\Models\User,id',
            'training' => 'required|numeric|exists:App\Models\PilotTraining,id',
            'url' => 'required|url',
            'score' => 'required|numeric|min:0|max:100',
        ]);

        $user = User::find($data['user']);
        $training = PilotTraining::find($data['training']);

        $exam = Exam::create([
            'type' => 'THEORY',
            'pilot_training_id' => $training->id,
            'pilot_rating_id' => $training->pilotRatings()->first()->id,
            'url' => $data['url'],
            'score' => $data['score'],
            'user_id' => $user->id,
            'issued_by' => \Auth::user()->id,
        ]);

        if ($user->setting_notify_newreport) {
            $user->notify(new ExamNotification($training, $exam));
        }

        PilotTrainingActivityController::create($training->id, 'EXAM', null, null, Auth::user()->id, 'Theory exam result added');

        return redirect(route('pilot.training.show', $training->id))->withSuccess($user->name . "'s theory result saved");
    }

    public function storePractical(Request $request)
    {
        $this->authorize('store', [Exam::class]);

        $data = [];
        $data = request()->validate([
            'user' => 'required|numeric|exists:App\Models\User,id',
            'training' => 'required|numeric|exists:App\Models\PilotTraining,id',
            'result' => 'required',
            'files.*' => 'sometimes|file|mimes:pdf,xls,xlsx,doc,docx,txt,png,jpg,jpeg',
        ]);

        $user = User::find($data['user']);
        $training = PilotTraining::find($data['training']);

        $exam = Exam::create([
            'type' => 'PRACTICAL',
            'pilot_training_id' => $training->id,
            'pilot_rating_id' => $training->pilotRatings()->first()->id,
            'result' => $data['result'],
            'user_id' => $user->id,
            'issued_by' => \Auth::user()->id,
        ]);

        unset($data['files']);

        ExamObjectAttachmentController::saveAttachments($request, $exam);

        if ($user->setting_notify_newreport) {
            $user->notify(new ExamNotification($training, $exam));
        }

        PilotTrainingActivityController::create($training->id, 'EXAM', null, null, Auth::user()->id, 'Practical exam result added');

        return redirect(route('pilot.training.show', $training->id))->withSuccess($user->name . "'s practical result saved");
    }
}
