<?php

namespace App\Http\Controllers;

use App\Http\Requests\Saldo\StoreRequest;
use App\Jobs\Saldo\CompareJob;
use App\Models\Saldo;
use App\Models\SaldoFile;
use App\Support\Enum\Saldo\CompareType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SaldoController extends Controller
{
    protected static array $actionList = [
        'index',
        'store',
        'destroy',
    ];

    protected static array $actionNames = [
        'destroy' => 'cancel',
    ];

    public function __construct()
    {
        $this->middleware('auth.basic');
    }

    public static function additionalRoutes(): void
    {
        Route::get('/saldo/{SaldoFile}/download', [static::class, 'download'])
            ->name('saldo.download');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response|View
    {
        $saldos = $request
            ->user()
            ->tasks()
            ->orderBy('created_at', 'desc')
            ->paginate();

        return view('saldo.index', compact('saldos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws \Throwable
     */
    public function store(StoreRequest $request): Response|RedirectResponse
    {
        DB::beginTransaction();

        /** @var \App\Models\Saldo $saldo */
        $saldo = tap(new Saldo([
            'compare_type' => CompareType::cast(collect($request->input('compare_type'))->sum()),
        ]))->save();

        $saldo
            ->files()
            ->saveMany(collect($request->file('dropzone-file'))
                ->map(fn (UploadedFile $uploadedFile) => new SaldoFile([
                    'name' => $uploadedFile->getClientOriginalName(),
                    'disk' => Storage::getDefaultDriver(),
                    'path' => $uploadedFile->storeAs(
                        $uploadedFile->optimizedPath('saldo'),
                        $uploadedFile->hashName(),
                        [
                            'disk' => Storage::getDefaultDriver(),
                        ],
                    ),
                ]))
            );

        DB::commit();

        CompareJob::dispatch($saldo);

        return redirect()->back();
    }

    public function download(SaldoFile $file): BinaryFileResponse
    {
        return response()->download(
            Storage::disk($file->disk)
                ->path($file->path),
            $file->name,
            [
                'Content-Type' => Storage::disk($file->disk)
                    ->mimeType($file->path),
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Saldo $saldo): Response|RedirectResponse
    {
        if ($saldo->status->canDelete()) {
            $saldo->delete();
        }

        return redirect()->back();
    }
}
