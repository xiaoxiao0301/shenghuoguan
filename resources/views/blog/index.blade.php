@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>{{ config('blog.title') }}</h1>
        <h5>Page {{ $posts->currentPage() }} of {{ $posts->lastPage() }}</h5>
        <hr>
        <ul>
            @foreach ($posts as $post)
                <li>
                    <a href="{{ route('blog.detail', ['slug' => $post->slug]) }}">{{ $post->title }}</a>
                    <em>({{ $post->published_at }})</em>
                    <p>
                        {{ \Illuminate\Support\Str::limit($post->content) }}
                    </p>
                </li>
            @endforeach
        </ul>
        <hr>
        {!! $posts->render() !!}
    </div>
@endsection



