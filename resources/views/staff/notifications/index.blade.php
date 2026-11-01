@extends('layouts.admin')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-black">Notifikasi</h1>

        @if ($notifications->total() > 0)
            <form action="{{ route('staff.notifications.readAll') }}" method="POST">
                @csrf
                <button type="submit" class="rounded-xl bg-slate-100 dark:bg-white/10 px-4 py-2 text-sm font-bold hover:bg-slate-200 dark:hover:bg-white/20">
                    Tandai semua sudah dibaca
                </button>
            </form>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <div class="rounded-[28px] border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 p-10 text-center">
            <p class="text-slate-500 dark:text-slate-400">Belum ada notifikasi.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($notifications as $notification)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border p-4
                    {{ $notification->read_at === null
                        ? 'border-amber-200 dark:border-amber-500/20 bg-amber-50 dark:bg-amber-500/10'
                        : 'border-slate-200 dark:border-white/10 bg-white dark:bg-white/5' }}">

                    <div>
                        @if ($notification->read_at === null)
                            <span class="mr-2 inline-block h-2 w-2 rounded-full bg-amber-500"></span>
                        @endif

                        <span class="text-sm">
                            {{ $notification->data['message'] ?? 'Notifikasi' }}
                        </span>

                        <p class="mt-1 text-xs text-slate-400">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>

                    @if ($notification->read_at === null)
                        <form action="{{ route('staff.notifications.read', $notification->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-cyan-600 hover:underline">
                                Tandai dibaca
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
@endsection
