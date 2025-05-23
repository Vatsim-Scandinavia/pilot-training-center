@extends('layouts.app')

@section('title', 'Application')
@section('content')

<div id="application">
    
    <form id="training-form" action="{{ route('pilot.training.store') }}" method="post">
        @csrf
        <!-- Information about training -->
        <div class="row" v-show="step === 1">
            <div class="col-xl-6 col-lg-12 col-md-12 mb-12">
                <div class="card shadow mb-4 border-left-warning">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 fw-bold text-primary">Important information</h6>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-graduation-cap"></i>&nbsp;What is Pilot Training?</h5>
                        <p class="card-text">Welcome to the Pilot Training Department of VATSIM Scandinavia. To achieve an Pilot rating you have to go through both theoretical and practical training and exams. You will be given all the necessary training documentation and will receive guidance by an instructor throughout the course. You will learn everything you need to know to be compliant with VATSIM PTD Member Certification Standards as well as the local procedures relevant to your area.</p>
                        <hr>
                        <h5 class="card-title"><i class="fas fa-user"></i>&nbsp;What do we expect from you?</h5>
                        <p class="card-text">First of all, we expect that you take the training seriously and for you to show up on time and prepared for your online training sessions. We also expect that you respect that everyone in the Pilot Training Department is doing this as a hobby in their spare time. You have to be able to study on your own as part of the training program is designed as a self-study.</p>
                        <hr>
                        <h5 class="card-title"><i class="fas fa-chalkboard-teacher"></i>&nbsp;What should you expect from us?</h5>
                        <p class="card-text">You should expect that we will help you as best as we can to prepare you for your skill test. You will be assigned to an instructor that will guide you on the way, and you should expect him to take you and your time seriously and to adapt the training to your level of competence.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-12 col-md-12 mb-12">
                <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 fw-bold text-primary">Training options</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-6 col-md-6 mb-12">
                                    <label class="form-label my-1 me-2" for="ratingSelect">Training type</label>
                                    <select id="ratingSelect" name="training_level" @change="ratingSelectChange($event)" class="form-select my-1 me-sm-2">
                                        <option v-if="ratings.length == 0" selected disabled>None available</option>
                                        <option v-for="rating in ratings" :value="rating.id">
                                            @{{ rating.name }}
                                        </option>
                                    </select> 
                                    <span v-show="errArea" class="text-danger" style="display: none">Select available rating</span>
                                </div>
                            </div>
                            <div v-show="errHours" id="errHours" class="text-danger" style="display: none">You need to fulfill the hour requirement before applying for this option.</div>

                            <a class="btn btn-success mt-2" href="#" v-on:click="next">Continue</a>
                        </div>
                    </div>
            </div>
        </div>

        <!-- Training Agreement -->
        <div class="row" style="display: none" v-show="step === 2">
            <div class="col-xl-12 col-md-12 mb-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 fw-bold text-primary">Operations Manual</h6>
                    </div>
                    <div class="card-body">

                        @if(Str::of(Setting::get('trainingSOP'))->endsWith('.pdf'))
                            <p>Please familiarize yourself with the Training Agreement below, and accept the terms by continuing to the next step. If you can't see the document below, <a href="{{ Setting::get('trainingSOP') }}" target="_blank">click here</a>.</p>
                            <embed src="{{ Setting::get('trainingSOP') }}" type="application/pdf" type="text/html" width="100%" height="800px">    
                        @else
                            <p>Please read through the <a href="{{ Setting::get('trainingSOP') }}" target="_blank">policy for students</a> and accept the terms by continuing to the next step.</p>
                        @endif

                        <a class="btn btn-success"  href="#" v-on:click="next">I accept</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="row" style="display: none" v-show="step === 3">
            <div class="col-xl-12 col-md-12 mb-12">

                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 fw-bold text-primary">Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-6 col-lg-12 col-md-12 mb-12">
                                <div class="mb-3">
                                    <label class="form-label" for="experience">Experience level</label>
                                    <select class="form-select" name="experience" id="experience">
                                        <option selected disabled>Choose best fitting level...</option>
                                        @foreach(\App\Http\Controllers\PilotTrainingController::$experiences as $id => $data)
                                            <option value="{{ $id }}">{{ $data["text"] }}</option>
                                        @endforeach
                                    </select>
                                    <span v-show="errExperience" class="text-danger" style="display: none">Please select a proper experience level</span>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="englishOnly" name="englishOnly" value="true">
                                    <label class="form-check-label" for="englishOnly">I'm <u>only</u> able to receive training in English instead of local language</label>
                                </div>

                                <hr>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="wantRemark" v-model="remarkChecked">
                                    <label class="form-check-label" for="wantRemark">I've an important remark about my training I would like to add</label>
                                </div>

                                <div class="mb-3" v-show="remarkChecked">
                                    <label class="form-label" for="remarkTextarea">Remark</label>
                                    <textarea class="form-control" name="comment" id="remarkTextarea" rows="2" placeholder="Enter important information regarding your application such as experience or something else we should know" maxlength="500"></textarea>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-12 col-md-12 mb-12">
                                <img class="d-none d-xl-block m-auto w-50 px-3 px-sm-4 mt-3 mb-4" src="{{asset('images/undraw_files_6b3d.svg')}}" alt="" height="100%">
                            </div>

                        </div>

                        <button type="submit" id="training-submit-btn" class="btn btn-success" v-on:click="submit">Submit training request<div class="submit-spinner spinner-border spinner-border-sm" role="status">&nbsp;</div></button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('js')
@vite('resources/js/vue.js')
<script>

    document.addEventListener("DOMContentLoaded", function () {

        var payload = {!! json_encode($payload, true) !!}
        var atcHours = {!! json_encode($pilot_hours, true) !!}
        const application = createApp({
            data(){
                return {
                    step: 1,
                    ratings: payload,
                    errRating: 0,
                    errHours: 0,
                    errExperience: false,
                    remarkChecked: 0,
                }
            },
            methods:{
                next() {
                    if(this.validate(this.step)) this.step++;
                },
                validate(page){
                    var validated = true
                    if(page == 1){
                        
                        let trainingLevel = Array.from(document.getElementById('ratingSelect').options).find(option => option.selected && !option.disabled)?.value;

                        if (trainingLevel == null) {
                            document.getElementById('ratingSelect').classList.add('is-invalid')
                            this.errRating = true;
                            validated = false;
                        }

                    } else if(page == 2){
                        validated = true;
                    }

                    return validated
                },
                submit(event) {
                    event.preventDefault();

                    // Reset errors
                    this.errExperience = false;
                    this.errLOM = false;
                    document.getElementById('experience').classList.remove('is-invalid');

                    // Validate
                    let trainingExperience = Array.from(document.getElementById('experience').options).find(option => option.selected && !option.disabled)?.value;
                    var errored = false;

                    if(trainingExperience == null){
                        document.getElementById('experience').classList.add('is-invalid')
                        this.errExperience = true;
                    }

                    // Submit form if validation is successful
                    if(!this.errExperience){
                        document.getElementById('training-submit-btn').disabled = true;
                        document.querySelector('.submit-spinner').style.display = 'inherit';
                        document.getElementById('training-form').submit();
                    }
        
                },
                ratingSelectChange(event){
                    document.getElementById('ratingSelect').classList.remove('is-invalid');
                    this.errHours = false;
                },
            }
        }).mount('#application');
    })

</script>
@endsection
