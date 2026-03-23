@extends('layouts.superadmin')

@section('content')
<section>
    <div class="panel panel-default">
        <div class="panel-heading">Legal Documents</div>

        <div class="panel-body">
            @if(Session::has('success'))
                <p class="alert alert-success">{{ Session::get('success') }}</p>
            @endif
            @foreach (['danger', 'warning', 'info'] as $msg)
                @if(Session::has('alert-' . $msg))
                    <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }}</p>
                @endif
            @endforeach

            <div style="margin-bottom: 16px;">
                @foreach($documentTypes as $typeValue => $typeLabel)
                    <a href="{{ route('admin.legal-documents.create', ['type' => $typeValue]) }}" class="btn btn-primary" style="margin-right: 8px;">
                        + New {{ $typeLabel }} version
                    </a>
                @endforeach
            </div>

            @foreach($documentTypes as $typeValue => $typeLabel)
                @php $typeDocs = $documents->get($typeValue, collect()); @endphp
                <h4>{{ $typeLabel }}</h4>

                @if($typeDocs->isEmpty())
                    <p class="text-muted" style="margin-bottom: 24px;">No versions yet.</p>
                @else
                    <table class="table table-hover table-striped table-condensed" style="margin-bottom: 32px;">
                        <thead>
                            <tr>
                                <th>Version</th>
                                <th>Title (EN)</th>
                                <th>Status</th>
                                <th>Published At</th>
                                <th>Created By</th>
                                <th>Sections</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($typeDocs as $doc)
                                <tr>
                                    <td><strong>v{{ $doc->version }}</strong></td>
                                    <td>{{ $doc->getTranslation('title', 'en') }}</td>
                                    <td>
                                        @if($doc->is_published)
                                            <span class="label label-success">Published</span>
                                        @else
                                            <span class="label label-default">Draft</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $doc->published_at ? $doc->published_at->format('Y-m-d H:i') : '—' }}
                                    </td>
                                    <td>{{ $doc->author->name ?? '—' }}</td>
                                    <td>{{ $doc->sections_count ?? '?' }}</td>
                                    <td align="right">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.legal-documents.show', $doc) }}" class="btn btn-default btn-xs">View</a>
                                            @if(!$doc->is_published)
                                                <form method="POST" action="{{ route('admin.legal-documents.publish', $doc) }}" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-xs"
                                                        onclick="return confirm('Publish this version? It will become the active document visible to all users.')">
                                                        Publish
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.legal-documents.destroy', $doc) }}"
                                                      style="display:inline;"
                                                      onsubmit="return confirm('Delete this draft? This cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endsection
