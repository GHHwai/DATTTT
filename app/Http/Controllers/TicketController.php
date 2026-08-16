<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    private const ATTACHMENT_RULES = ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt,csv,zip'];

    /**
     * Employees see only their own tickets. Anyone with tickets.view.all
     * (IT staff, admins) sees the shared queue with filter tabs.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->hasPermission('tickets.view.all')) {
            $filter = $request->query('filter', 'unclaimed');

            $tickets = Ticket::with(['user', 'assignee'])
                ->when($filter === 'unclaimed', fn ($q) => $q->whereNull('assigned_to')->whereNotIn('status', ['resolved', 'closed']))
                ->when($filter === 'mine', fn ($q) => $q->where('assigned_to', $user->id)->whereNotIn('status', ['resolved', 'closed']))
                ->when($filter === 'closed', fn ($q) => $q->whereIn('status', ['resolved', 'closed']))
                ->latest('updated_at')
                ->paginate(15)
                ->withQueryString();

            return view('tickets.index', ['tickets' => $tickets, 'filter' => $filter]);
        }

        $tickets = $user->tickets()->latest()->paginate(15);

        return view('tickets.index', ['tickets' => $tickets, 'filter' => null]);
    }

    public function create(): View
    {
        return view('tickets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'in:hardware,software,network,account,other'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => self::ATTACHMENT_RULES,
        ]);

        $ticket = $request->user()->tickets()->create(
            collect($validated)->except('attachments')->toArray() + ['status' => 'open']
        );

        $this->storeAttachments($request, $ticket);

        return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket submitted — IT has been notified.');
    }

    public function show(Request $request, Ticket $ticket): View
    {
        $this->authorizeTicketAccess($request, $ticket);

        $ticket->load([
            'user',
            'assignee',
            'attachments',
            'comments' => fn ($q) => $q->with(['user', 'attachments'])->oldest(),
        ]);

        return view('tickets.show', compact('ticket'));
    }

    public function claim(Request $request, Ticket $ticket): RedirectResponse
    {
        if (! $ticket->isUnclaimed()) {
            return back()->with('status', 'That ticket has already been claimed.');
        }

        $ticket->update([
            'assigned_to' => $request->user()->id,
            'status' => 'in_progress',
        ]);

        return back()->with('status', "Ticket claimed — it's yours to work on.");
    }

    public function unclaim(Request $request, Ticket $ticket): RedirectResponse
    {
        if ($ticket->assigned_to !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        $ticket->update(['assigned_to' => null, 'status' => 'open']);

        return back()->with('status', 'Ticket released back to the queue.');
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:in_progress,resolved,closed'],
            'resolution_notes' => ['nullable', 'string', 'max:4000'],
        ]);

        $ticket->update([
            'status' => $validated['status'],
            'resolution_notes' => $validated['resolution_notes'] ?? $ticket->resolution_notes,
            'resolved_at' => in_array($validated['status'], ['resolved', 'closed'], true) ? now() : null,
        ]);

        return back()->with('status', 'Ticket updated.');
    }

    public function comment(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorizeTicketAccess($request, $ticket);

        $validated = $request->validate([
            'body' => ['nullable', 'required_without:attachments', 'string', 'max:4000'],
            'attachments' => ['nullable', 'array', 'max:5', 'required_without:body'],
            'attachments.*' => self::ATTACHMENT_RULES,
        ]);

        $comment = $ticket->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'] ?? '',
        ]);

        $this->storeAttachments($request, $comment);

        return back()->with('status', 'Comment added.');
    }

    /**
     * Stream an attachment's original file back to the browser, after
     * confirming the requester has access to the parent ticket.
     */
    public function downloadAttachment(Request $request, Ticket $ticket, Attachment $attachment): StreamedResponse
    {
        $this->authorizeTicketAccess($request, $ticket);

        $belongsToTicket = $attachment->attachable_type === Ticket::class
            && $attachment->attachable_id === $ticket->id;

        $belongsToComment = $attachment->attachable_type === TicketComment::class
            && $attachment->attachable?->ticket_id === $ticket->id;

        abort_unless($belongsToTicket || $belongsToComment, 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    /**
     * Persist any uploaded files against the given ticket or comment.
     * Files are stored on the private "local" disk, never publicly served.
     */
    private function storeAttachments(Request $request, Ticket|TicketComment $attachable): void
    {
        foreach ($request->file('attachments', []) as $file) {
            $path = $file->store('ticket-attachments', 'local');

            $attachable->attachments()->create([
                'user_id' => $request->user()->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    /**
     * Employees may only view/comment on their own tickets. Anyone with
     * tickets.view.all (IT staff, admins) can access any ticket.
     */
    private function authorizeTicketAccess(Request $request, Ticket $ticket): void
    {
        $user = $request->user();

        if ($user->hasPermission('tickets.view.all')) {
            return;
        }

        if ($ticket->user_id !== $user->id) {
            abort(403);
        }
    }
}
