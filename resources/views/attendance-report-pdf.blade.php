<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Completed Attendance</title>
    <style>
        @page { margin: 15mm; size: A4; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; line-height: 1.3; }
        h1 { font-size: 14px; margin-bottom: 4px; }
        .period { font-size: 9px; color: #666; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
        th { background: #e9ecef; font-weight: bold; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>Completed Attendance</h1>
    @if(($periodDays ?? 0) > 0 && ($date_from ?? '') && ($date_to ?? ''))
        <p class="period">Period: {{ $date_from }} to {{ $date_to }} ({{ $periodDays }} days)</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th class="text-center">Date</th>
                <th class="text-center">IN Time</th>
                <th class="text-center">OUT Time</th>
                <th class="text-center">Hours</th>
            </tr>
        </thead>
        <tbody>
            @foreach($completed ?? [] as $r)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $r['employeeName'] ?? '' }}</td>
                <td class="text-center">{{ $r['date'] ?? '' }}</td>
                <td class="text-center">{{ isset($r['inTime']) ? $r['inTime']->format('h:i A') : '' }}</td>
                <td class="text-center">{{ isset($r['outTime']) ? $r['outTime']->format('h:i A') : '' }}</td>
                <td class="text-center">{{ isset($r['hours']) ? number_format($r['hours'], 2) . ' hrs' : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if(count($completed ?? []) == 0)
    <p>No completed attendance records for the selected period.</p>
    @endif
</body>
</html>
