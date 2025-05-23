@extends('layouts.app')

@section('title', 'Training Report')
@section('content')


<div class="row">
    <div class="col-xl-5 col-lg-12 col-md-12 mb-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">
                    {{ $report->pilotTraining->user->first_name }}'s training {{ $report->report_date->toEuropeanDate() }}
                    @if($report->draft)
                        <span class='badge bg-danger'>Draft</span>
                    @endif
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('pilot.training.report.update', ['report' => $report->id]) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label" for="lesson">Lesson</label>
                        
                        <!-- Input to display the lesson name -->
                        <input
                            id="lesson_name"
                            class="form-control @error('lesson_id') is-invalid @enderror"
                            type="text"
                            name="lesson_name"
                            list="lessons"
                            value="{{ empty(old('lesson_name')) ? $report->lesson->name : old('lesson') }}"
                            value="{{ old('lesson_name') }}"
                            required>
                        
                        <!-- Hidden input to store and submit lesson_id -->
                        <input
                            type="hidden"
                            name="lesson_id"
                            id="lesson_id"
                            value="{{ old('lesson_id') }}">
                    
                        <!-- Datalist for lessons (only lesson names are displayed in the dropdown) -->
                        <datalist id="lessons">
                            @foreach ($lessons as $lesson)
                                <option value="{{ $lesson->name }}" data-id="{{ $lesson->id }}"></option>
                            @endforeach
                        </datalist>
                    
                        @error('lesson_id')
                            <span class="text-danger">{{ $errors->first('lesson_name') }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="date">Date</label>
                        <input id="date" class="datepicker form-control @error('report_date') is-invalid @enderror" type="text" name="report_date" value="{{ empty(old('report_date')) ? $report->report_date->toEuropeanDate() : old('report_date')}}" required>
                        @error('report_date')
                            <span class="text-danger">{{ $errors->first('report_date') }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="instructor_hours" class="form-label">Hours flown</label>
                        <input type="time" id="instructor_hours" class="form-control @error('instructor_hours') is-invalid @enderror" name="instructor_hours" value="{{ \App\Http\Controllers\PilotTrainingReportController::decimalToTime($report->instructor_hours) }}" required>
                        @error('instructor_hours')
                            <span class="text-danger">{{ $errors->first('instructor_hours') }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="contentBox">Session Details</label>
                        <textarea class="form-control @error('content') is-invalid @enderror" name="content" id="contentBox" rows="8" placeholder="Write the report here.">{{ empty(old('content')) ? $report->content : old('content') }}</textarea>
                        @error('content')
                            <span class="text-danger">{{ $errors->first('content') }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="contentimprove">Report</label>
                        <textarea class="form-control @error('contentimprove') is-invalid @enderror" name="contentimprove" id="contentimprove" rows="4" placeholder="In which areas do the student need to improve?">{{ empty(old('contentimprove')) ? $report->contentimprove : old('contentimprove') }}</textarea>
                        @error('contentimprove')
                            <span class="text-danger">{{ $errors->first('contentimprove') }}</span>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" value="1" class="form-check-input @error('draft') is-invalid @enderror" name="draft" id="draftCheck" {{ $report->draft ? "checked" : "" }}>
                        <label class="form-check-label" name="draft" for="draftCheck">Save as draft</label>
                        @error('draft')
                            <span class="text-danger">{{ $errors->first('draft') }}</span>
                        @enderror
                    </div>

                    @if (\Illuminate\Support\Facades\Gate::inspect('update', $report)->allowed())
                        <button type="submit" class="btn btn-success">Update report</button>
                    @endif

                    @if (\Illuminate\Support\Facades\Gate::inspect('delete', $report)->allowed())
                        <a href="{{ route('pilot.training.report.delete', $report->id) }}" class="btn btn-danger" id="delete-btn" data-report="{{ $report->id }}">Delete report</a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-5 col-lg-12 col-md-12 mb-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">
                    Manage attachments
                </h6>
            </div>
            <div class="card-body">

                <div>
                    @if(count($report->attachments) == 0)
                        <i>This report has no attachments.</i>
                    @endif

                    @foreach($report->attachments as $attachment)
                        <div data-id="{{ $attachment->id }}">
                            <a href="{{ route('pilot.training.object.attachment.show', ['attachment' => $attachment]) }}" target="_blank">
                                {{ $attachment->file->name }}
                            </a>
                            <i data-attachment="{{ $attachment->id }}" class="fa fa-lg fa-trash text-danger deleteAttachmentBtn" style="cursor: pointer;"></i>
                        </div>
                    @endforeach
                </div>

                <hr>

                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    Please save your report before uploading attachments to avoid losing your changes.
                </div>

                <form method="post" id="file-form" action="{{ route('pilot.training.object.attachment.store', ['trainingObjectType' => 'report', 'trainingObject' => $report->id]) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="attachments">Attachments</label>
                        <div>
                            <input type="file" name="file" id="add-file" class="@error('file') is-invalid @enderror" accept=".pdf, .xls, .xlsx, .doc, .docx, .txt, .png, .jpg, .jpeg" onchange="uploadFile(this)" multiple>
                        </div>
                        @error('file')
                            <span class="text-danger">{{ $errors->first('file') }}</span>
                        @enderror
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>


@endsection

@section('js')

<!-- Attachment management -->
<script>

    function uploadFile(input) {
        if (input.value != null) {
            document.getElementById('file-form').submit()
        }
    }

    var deleteAttachmentBtn = document.querySelectorAll('.deleteAttachmentBtn');
    deleteAttachmentBtn.forEach(function (btn) {
        btn.addEventListener('click', function () {

            let id = btn.dataset.attachment;

            fetch('/pilot/training/attachment/'+id, {
                method: "POST",
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': "{!! csrf_token() !!}"
                },
                body: '_method=DELETE'
            })
            .then(response => {
                if (response.ok) {
                    document.querySelector('div[data-id="' + id + '"]').remove();
                }
            })
            .catch(error => {
                console.error("An error occurred while attempting to delete attachment:", error);
            });
        });
    });
</script>

<!-- Flatpickr -->
@vite(['resources/js/flatpickr.js', 'resources/sass/flatpickr.scss'])
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var defaultDate = "{{ empty(old('created_at')) ? \Carbon\Carbon::make($report->created_at)->format('d/m/Y') : old('created_at') }}"
        document.querySelector('.datepicker').flatpickr({ disableMobile: true, minDate: "{!! date('Y-m-d', strtotime('-1 months')) !!}", dateFormat: "d/m/Y", defaultDate: new Date("{{ $report->report_date }}"), locale: {firstDayOfWeek: 1 } });
    });
</script>

<!-- Markdown Editor -->
@vite(['resources/js/easymde.js', 'resources/sass/easymde.scss'])
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var simplemde1 = new EasyMDE({ 
            element: document.getElementById("contentBox"), 
            status: false, 
            toolbar: ["bold", "italic", "heading-3", "|", "quote", "unordered-list", "ordered-list", "|", "link", "preview", "side-by-side", "fullscreen", "|", "guide"],
            insertTexts: {
                link: ["[","](link)"],
            }
        });
        var simplemde2 = new EasyMDE({ 
            element: document.getElementById("contentimprove"), 
            status: false, 
            toolbar: ["bold", "italic", "heading-3", "|", "quote", "unordered-list", "ordered-list", "|", "link", "preview", "side-by-side", "fullscreen", "|", "guide"],
            insertTexts: {
                link: ["[","](link)"],
            }
        });
    })
</script>

<script>
    document.getElementById('lesson_name').addEventListener('input', function() {
        // Get the entered lesson name
        var lessonName = this.value;

        // Find the matching option in the datalist
        var options = document.querySelectorAll('#lessons option');
        var lessonId = '';

        options.forEach(function(option) {
            if (option.value === lessonName) {
                // Set the corresponding lesson_id
                lessonId = option.getAttribute('data-id');
            }
        });

        // Update the hidden input with the lesson_id
        document.getElementById('lesson_id').value = lessonId;

        console.log('Selected lesson_id:', lessonId);
    });
</script>

@endsection
