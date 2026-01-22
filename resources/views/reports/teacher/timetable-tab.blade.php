<div class="col-md-12 grid-margin stretch-card search-container">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div id="calendar-wrapper">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #calendar-wrapper {
        width: 100%;
        max-height: fit-content;    
        /* you can adjust this */
        overflow-y: auto;
        /* vertical scroll */
        overflow-x: auto;
    }

    #calendar {
        min-width: 900px;
        /* prevents squeezing on small screens */
    }
</style>

@section('js')
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // Load events
            @foreach($timetables as $timetable)
                teacherTimetable.addEvent({
                    title: "{{ $timetable->title }}",
                    daysOfWeek: [days.indexOf("{{ $timetable->day }}")],
                    startTime: "{{ $timetable->start_time }}",
                    endTime: "{{ $timetable->end_time }}",
                    color: "{{ $timetable->subject->bg_color ?? 'Black' }}",
                    id: "{{ $timetable->id }}",
                    class_section: "{{ $timetable->class_section->full_name }}"
                });
            @endforeach

            // Apply slot options once
            teacherTimetable.setOption("slotMinTime", "{{ $timetableSettingsData['timetable_start_time'] ?? '00:00:00' }}");
            teacherTimetable.setOption("slotMaxTime", "{{ $timetableSettingsData['timetable_end_time'] ?? '23:59:00' }}");
            teacherTimetable.setOption("slotDuration", "{{ $timetableSettingsData['timetable_duration'] ?? '00:30:00' }}");
        });

        // IMPORTANT: Fix Responsive Calendar in Bootstrap Tabs
        $('a[data-toggle="tab"][href="#timetable"]').on('shown.bs.tab', function () {

            setTimeout(function () {

                if (teacherTimetable) {

                    // destroy → FULL CLEAN RESET
                    teacherTimetable.destroy();

                    // re-render calendar properly
                    teacherTimetable.render();

                    // force size recalculation → MOST IMPORTANT for responsiveness
                    teacherTimetable.updateSize();
                }

            }, 50); // small delay ensures tab DOM is visible
        });
    </script>
@endsection