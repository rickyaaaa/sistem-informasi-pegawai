<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Satker') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('satker.store') }}" method="POST">
                        @include('satker._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

