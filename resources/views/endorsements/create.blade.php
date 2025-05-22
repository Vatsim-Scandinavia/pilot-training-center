@extends('layouts.app')

@section('title', 'Edit Endorsements')
@section('content')

<div class="row" id="giveEndorsements">
    <div class="col-xl-5 col-md-12 mb-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">
                    Edit Endorsements
                </h6> 
            </div>
            <div class="card-body" id="training-selector">
                <form id="endorsementForm" action="{!! action('EndorsementController@store') !!}" method="POST">
                    @csrf

                    {{-- User --}} 
                    <div class="mb-3">
                        <label class="form-label" for="user">Instructor</label>
                        <input
                            id="user"
                            class="form-control"
                            type="text"
                            name="user"
                            list="userList"
                            v-model="user"
                            v-bind:class="{'is-invalid': (validationError && !user)}"
                            value="{{ $prefillUserId }}">

                        <datalist id="userList">
                            @foreach($users as $user)
                                @browser('isFirefox')
                                    <option>{{ $user->id }}</option>
                                @else
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endbrowser
                            @endforeach
                        </datalist>
                    </div>

                    <label class="form-label my-1 me-2" for="ratingSelect">Training levels: <span class="badge bg-secondary">Ctrl/Cmd+Click</span> to select multiple</label>
                    <select multiple id="ratingSelect" name="rating[]" class="form-select @error('ratings') is-invalid @enderror" size="5">
                        @foreach ($ratings as $rating)
                            <option value="{{ $rating->id }}"
                                {{ $user->instructorEndorsements->contains($rating->id) ? 'selected' : '' }}>
                                {{ $rating->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" id="submit_btn" class="btn btn-success mt-4">Save</button>
                </form>
            </div>
        </div>
    </div>

</div>

@endsection

@section('js')
@vite('resources/js/vue.js')

<script>
    window.userEndorsementsMap = @json($userEndorsementsMap);
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const app = createApp({
            data() {
                return {
                    ratings: {!! json_encode($ratings) !!},
                    user: '{{ $prefillUserId ?? '' }}',
                    selectedEndorsements: [],
                }
            },
            mounted() {
                if (this.user) {
                    this.updateSelectedEndorsements(this.user);
                }
            },
            methods: {
                updateSelectedEndorsements(userId) {
                    this.selectedEndorsements = window.userEndorsementsMap[userId] || [];
                    this.applySelectedOptions();
                },
                applySelectedOptions() {
                    const select = document.getElementById('ratingSelect');
                    Array.from(select.options).forEach(option => {
                        option.selected = this.selectedEndorsements.includes(Number(option.value));
                    });
                }
            },
            watch: {
                user(newUser) {
                    this.updateSelectedEndorsements(newUser);
                }
            }
        });
        app.mount('#training-selector');
    });
</script>

@endsection
