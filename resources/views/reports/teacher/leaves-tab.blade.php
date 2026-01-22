<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-md-4">
            <label><strong>Select Month</strong></label>
            <select id="leave_month" class="form-control">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endfor
            </select>
        </div>
    </div>

    <div class="card">
        <div class="card-header p-2">
            <h5 class="mb-0">Leave Details</h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody id="leave_table_body">
                        <tr>
                            <td colspan="5" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        loadLeaveData();

        document.getElementById('leave_month').addEventListener('change', loadLeaveData);

        function loadLeaveData() {
            const month = document.getElementById('leave_month').value;
            const year = new Date().getFullYear();
            const teacherId = "{{ $teacher->id }}";

            document.getElementById('leave_table_body').innerHTML =
                '<tr><td colspan="5" class="text-center">Loading...</td></tr>';

            fetch(`{{ route('reports.teacher.leave.report') }}?teacher_id=${teacherId}&month=${month}&year=${year}`)
                .then(res => res.json())
                .then(data => renderLeaves(data))
                .catch(err => {
                    console.error(err);
                    document.getElementById('leave_table_body').innerHTML =
                        '<tr><td colspan="5" class="text-center text-danger">Failed to load</td></tr>';
                });
        }

        function renderLeaves(data) {
            const tbody = document.getElementById('leave_table_body');

            if (!data.success || data.leaves.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No Leave Records</td></tr>';
                return;
            }

            tbody.innerHTML = "";

            data.leaves.forEach(l => {
                if (l.status == 1){
                    statusText = 'Approved';
                } else if (l.status == 0){
                    statusText = 'Pending';
                } else {
                    statusText = 'Rejected';
                }
                tbody.innerHTML += `
                <tr>
                    <td>${l.date_formatted}</td>
                    <td>${l.type}</td>
                    <td>${l.reason ?? '-'}</td>
                    <td>${statusText}</td>
                </tr>
            `;
            });
        }
    });
</script>