@extends('layouts.app')

@section('layout-guest')
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        {{-- **** --}}
        <div id="layoutAuthentication_footer">
            @includeIf('layouts.includes.footer')
        </div>
    </div>
@endsection
