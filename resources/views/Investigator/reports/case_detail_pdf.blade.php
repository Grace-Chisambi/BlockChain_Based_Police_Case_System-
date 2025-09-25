<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Case Report – {{ $case->case_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.6;
        }
        .header, .section {
            margin-bottom: 25px;
        }
        .title {
            font-size: 20px;
            color: #0d6efd;
            font-weight: bold;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            padding: 5px;
            background: #f0f0f0;
            margin-bottom: 10px;
        }
        .text-muted {
            color: #666;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            background-color: #ccc;
            border-radius: 4px;
            margin-left: 5px;
        }
        hr {
            border: 0;
            border-top: 1px solid #ccc;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="title">Case Report – {{ $case->case_number }}</div>
        <p><strong>Date:</strong> {{ now()->format('Y-m-d') }}</p>
        <p><strong>Time:</strong> {{ now()->format('H:i') }}</p>
        <p><strong>Reported By:</strong> {{ $user->fname }} {{ $user->sname }}</p>
    </div>

    {{-- Complaint --}}
    <div class="section">
        <div class="section-title">Complaint Statement ({{ $complaint->sname }})</div>
        <div>{!! $complaint->statement !!}</div>
    </div>

    {{-- Suspects --}}
    <div class="section">
        <div class="section-title">Suspect Statements</div>
        @if($suspects->isNotEmpty())
            @foreach($suspects as $suspect)
                <p><strong>{{ $suspect->fname }} {{ $suspect->sname }}</strong></p>
                <div>{!! $suspect->statement !!}</div>
                <hr>
            @endforeach
        @else
            <p class="text-muted">No suspect statements available.</p>
        @endif
    </div>

    {{-- Investigation Progress --}}
    <div class="section">
        <div class="section-title">Investigation Progress</div>
        @forelse($progress as $entry)
            <p>
                <strong>{{ \Carbon\Carbon::parse($entry->date)->format('d M Y H:i') }}</strong>
                @if(!empty($entry->entry_type))
                    <span class="badge">{{ ucfirst($entry->entry_type) }}</span>
                @endif
            </p>
            <div>{!! $entry->notes !!}</div>
            <hr>
        @empty
            <p class="text-muted">No progress updates available.</p>
        @endforelse
    </div>

    {{-- Closure --}}
    <div class="section">
        <div class="section-title">Case Closure Statement</div>
        @if($closure)
            <p><strong>Type:</strong> {{ $closure->closure_type }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($closure->closure_date)->format('d M Y') }}</p>
            <div>{!! $closure->reason !!}</div>
        @else
            <p class="text-muted">No closure statement available.</p>
        @endif
    </div>

</body>
</html>
