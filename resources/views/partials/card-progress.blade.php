<div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
        <thead class="table-primary">
            <tr>
                <th scope="col">Date</th>
                <th scope="col">Officer</th>
                <th scope="col">Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($progressEntries as $progress)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($progress->date)->format('M d, Y') }}</td>
                    <td>{{ $progress->fname }} {{ $progress->sname }}</td>
                    <td>{{ $progress->notes }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">
                        No progress updates available for this case.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
