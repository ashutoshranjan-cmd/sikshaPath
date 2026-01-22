{{-- ========================================= --}}
{{-- CLASS TEACHER OF (Separate Simple Card) --}}
{{-- ========================================= --}}
<div class="card mt-3 mb-3">
    <div class="card-header bg-gradient-light p-2">
        <h5 class="mb-0 text-theme">{{ __('class_teacher_of') }}</h5>
    </div>

    <div class="card-body p-2">
        @if($teacher->staff && $teacher->staff->class_teacher->count())
            <div class="row">
                @foreach ($teacher->staff->class_teacher as $ct)
                    @php $cs = $ct->class_section; @endphp

                    <div class="col-md-4 mb-2">
                        <div class="border rounded p-2">
                            @if(!empty($cs->section))
                                <strong>{{ $cs->class->name ?? '' }} - {{ $cs->section->name }}</strong><br>
                            @else
                                <strong>{{ $cs->class->name ?? '' }}</strong><br>
                            @endif
                            <small class="text-muted">{{ __('medium') }}: {{ $cs->medium->name ?? '-' }}</small>
                        </div>
                    </div>

                @endforeach
            </div>
        @else
            <p class="text-muted mb-0">-</p>
        @endif
    </div>
</div>



{{-- ========================================= --}}
{{-- PREPARE UNIQUE CLASS SECTIONS --}}
{{-- ========================================= --}}
@php
    $uniqueClasses = $teacher->staff
        ? $teacher->staff->subjects->groupBy('class_section_id')
        : collect();
@endphp



{{-- ========================================= --}}
{{-- TEACHING OVERVIEW TABLE (Clear & Simple) --}}
{{-- ========================================= --}}
<div class="card">
    <div class="card-header bg-gradient-light p-2">
        <h5 class="mb-0 text-theme">{{ __('teaching_overview') }}</h5>
    </div>

    <div class="card-body p-0">

        @if($uniqueClasses->count())
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('class') }}</th>
                            <th>{{ __('section') }}</th>
                            <th>{{ __('medium') }}</th>
                            <th>{{ __('stream') }}</th>
                            <th>{{ __('subjects') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($uniqueClasses as $items)
                            @php $cs = $items->first()->class_section; @endphp

                            <tr>
                                <td>{{ $cs->class->name ?? '' }}</td>
                                <td>{{ $cs->section->name ?? '' }}</td>
                                <td>{{ $cs->medium->name ?? '-' }}</td>
                                <td>{{ $cs->class->stream->name ?? '-' }}</td>

                                <td>
                                    @foreach ($items as $sub)
                                        <span class="badge bg-primary mb-1">{{ $sub->subject->name_with_type }}</span>
                                    @endforeach
                                </td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted p-2 mb-0">-</p>
        @endif
    </div>
</div>