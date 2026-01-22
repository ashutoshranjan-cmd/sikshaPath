{{-- ================================ --}}
{{-- SALARY STRUCTURE (PARTIAL VIEW) --}}
{{-- ================================ --}}

<div class="card mt-3">
    <div class="card-header bg-light p-2">
        <h5 class="mb-0">Salary Structure</h5>
    </div>

    <div class="card-body p-2">

        {{-- ========================== --}}
        {{-- SUMMARY TABLE --}}
        {{-- ========================== --}}
        <table class="table table-bordered table-sm mb-3">
            <tbody>
                <tr>
                    <th width="40%">Basic Salary</th>
                    <td>{{ number_format($salary_structure['basic_salary'], 2) }}</td>
                </tr>

                <tr>
                    <th>Total Allowances</th>
                    <td>{{ number_format($salary_structure['total_allowance'], 2) }}</td>
                </tr>

                <tr>
                    <th>Total Deductions</th>
                    <td>{{ number_format($salary_structure['total_deduction'], 2) }}</td>
                </tr>

                <tr class="table-success">
                    <th>Net Salary</th>
                    <td><strong>{{ number_format($salary_structure['net_salary'], 2) }}</strong></td>
                </tr>
            </tbody>
        </table>



        {{-- ========================== --}}
        {{-- ALLOWANCES LIST --}}
        {{-- ========================== --}}
        <h6 class="mt-3 mb-1">Allowance Details</h6>

        @if(count($salary_structure['allowances']))
            <table class="table table-sm table-bordered mb-3">
                <thead class="table-light">
                    <tr>
                        <th>Allowance Name</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salary_structure['allowances'] as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-right">{{ number_format($item['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted">No Allowances</p>
        @endif



        {{-- ========================== --}}
        {{-- DEDUCTIONS LIST --}}
        {{-- ========================== --}}
        <h6 class="mt-3 mb-1">Deduction Details</h6>

        @if(count($salary_structure['deductions']))
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Deduction Name</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salary_structure['deductions'] as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-right">{{ number_format($item['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted">No Deductions</p>
        @endif

    </div>
</div>