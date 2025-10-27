<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Filters\SessionFilter;
use App\Http\Requests\Session\UpdateSessionRequest;
use App\Http\Resources\Session\SessionCollection;
use App\Http\Resources\Session\SessionResource;
use App\Models\Session;
use App\Services\Jit\Secrets\SecretsManager;
use App\Traits\IncludeRelationships;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SessionController extends Controller
{
    use IncludeRelationships;
    protected SecretsManager $secretsManager;

    public function __construct(SecretsManager $secretsManager)
    {
        $this->secretsManager = $secretsManager;
    }

    public function index(SessionFilter $filter, Request $request): SessionCollection
    {
        $this->authorize('viewAny', Session::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        // $sessions = Cache::remember(
        //     CacheKey::SESSIONS->key($request->get(config('pam.org.request_attribute'))),
        //     config('cache.default_ttl'),
        //     function () use ($filter, $pagination) {
        //         return Session::filter($filter)->paginate($pagination);
        //     }
        // );
        $sessions = Session::filter($filter)
            ->paginate($pagination);
        return new SessionCollection($sessions);
    }

    public function show(string $id): SessionResource
    {
        $sessionQuery = Session::query();
        $this->applyIncludes($sessionQuery, request());
        $session = $sessionQuery->findOrFail($id);
        $this->authorize('view', $session);

        return new SessionResource($session);
    }

    // public function update(UpdateSessionRequest $request, Session $session): SessionResource
    // {
    //     $this->authorize('update', $session);
    //     $validated = $request->validated();
    //     $session->update($validated);

    //     return new SessionResource($session);
    // }

    // public function destroy(Session $session): Response
    // {
    //     $this->authorize('delete', $session);
    //     $session->deleted_by = Auth::id();
    //     $session->save();
    //     $session->delete();

    //     return $this->noContent();
    // }

    // /**
    //  * Start a session and create JIT account
    //  */
    // public function start(Request $request, Session $session): JsonResponse
    // {
    //     // Validate session can be started
    //     if ($session->status !== 'scheduled') {
    //         return response()->json([
    //             'error' => 'Session is not in scheduled status',
    //         ], 400);
    //     }

    //     if (now()->lt($session->start_datetime) || now()->gt($session->scheduled_end_datetime)) {
    //         return response()->json([
    //             'error' => 'Current time is outside the approved session window',
    //         ], 400);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // Create JIT account
    //         $jitAccount = $this->secretsManager->createAccount($session);

    //         // Update session status
    //         $session->update([
    //             'status' => 'active',
    //             'checkin_at' => now(),
    //             'checkin_by' => Auth::id(),
    //             'is_checkin' => true,
    //         ]);

    //         // Prepare response with credentials
    //         $credentials = [
    //             'host' => $session->asset->host,
    //             'port' => $session->asset->port,
    //             'database' => $session->asset->database ?? $session->asset->name,
    //             'username' => $jitAccount->username,
    //             'password' => \Crypt::decryptString($jitAccount->password),
    //             'expires_at' => $jitAccount->expires_at,
    //         ];

    //         DB::commit();

    //         Log::info('Session started successfully', [
    //             'session_id' => $session->id,
    //             'user_id' => Auth::id(),
    //         ]);

    //         return response()->json([
    //             'message' => 'Session started successfully',
    //             'credentials' => $credentials,
    //             'session' => $session->fresh(),
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         Log::error('Failed to start session', [
    //             'session_id' => $session->id,
    //             'error' => $e->getMessage(),
    //         ]);

    //         return response()->json([
    //             'error' => 'Failed to start session: '.$e->getMessage(),
    //         ], 500);
    //     }
    // }

    // /**
    //  * End a session and terminate JIT account
    //  */
    // public function end(Request $request, Session $session): JsonResponse
    // {
    //     // Validate session can be ended
    //     if (!in_array($session->status, ['active', 'scheduled'])) {
    //         return response()->json([
    //             'error' => 'Session cannot be ended in current status',
    //         ], 400);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // Terminate JIT account
    //         $terminationResults = $this->secretsManager->terminateAccount($session);

    //         // Update session
    //         $session->update([
    //             'status' => 'ended',
    //             'end_datetime' => now(),
    //             'ended_at' => now(),
    //             'ended_by' => Auth::id(),
    //             'actual_duration' => now()->diffInMinutes($session->start_datetime),
    //             'session_note' => $request->input('note'),
    //         ]);

    //         DB::commit();

    //         Log::info('Session ended successfully', [
    //             'session_id' => $session->id,
    //             'termination_results' => $terminationResults,
    //         ]);

    //         return response()->json([
    //             'message' => 'Session ended successfully',
    //             'termination_results' => $terminationResults,
    //             'session' => $session->fresh(),
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         Log::error('Failed to end session', [
    //             'session_id' => $session->id,
    //             'error' => $e->getMessage(),
    //         ]);

    //         return response()->json([
    //             'error' => 'Failed to end session: '.$e->getMessage(),
    //         ], 500);
    //     }
    // }

    // /**
    //  * Terminate a session forcefully
    //  */
    // public function terminate(Request $request, Session $session): JsonResponse
    // {
    //     $request->validate([
    //         'reason' => 'required|string|max:500',
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         // Terminate JIT account
    //         $terminationResults = $this->secretsManager->terminateAccount($session);

    //         // Update session
    //         $session->update([
    //             'status' => 'ended',
    //             'is_terminated' => true,
    //             'terminated_at' => now(),
    //             'terminated_by' => Auth::id(),
    //             'end_datetime' => now(),
    //             'session_note' => 'Terminated: '.$request->input('reason'),
    //         ]);

    //         DB::commit();

    //         Log::warning('Session terminated by admin', [
    //             'session_id' => $session->id,
    //             'terminated_by' => Auth::id(),
    //             'reason' => $request->input('reason'),
    //         ]);

    //         return response()->json([
    //             'message' => 'Session terminated successfully',
    //             'termination_results' => $terminationResults,
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         Log::error('Failed to terminate session', [
    //             'session_id' => $session->id,
    //             'error' => $e->getMessage(),
    //         ]);

    //         return response()->json([
    //             'error' => 'Failed to terminate session: '.$e->getMessage(),
    //         ], 500);
    //     }
    // }

}
