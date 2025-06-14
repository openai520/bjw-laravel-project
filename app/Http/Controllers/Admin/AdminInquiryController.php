<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminInquiryController extends Controller
{
    /**
     * Display a listing of the inquiries.
     */
    public function index(): View
    {
        $inquiries = Inquiry::latest()->paginate(15);

        return view('admin.inquiries.index', compact('inquiries'));
    }

    /**
     * Display the specified inquiry.
     */
    public function show(Inquiry $inquiry): View
    {
        // 预加载关联数据
        $inquiry->loadMissing(['items.product']);

        return view('admin.inquiries.show', compact('inquiry'));
    }

    /**
     * Update the status of the specified inquiry.
     */
    public function updateStatus(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processed',
        ]);

        try {
            $oldStatus = $inquiry->status;
            $inquiry->status = $validated['status'];
            $inquiry->save();

            Log::info('Inquiry status updated', [
                'inquiry_id' => $inquiry->id,
                'old_status' => $oldStatus,
                'new_status' => $inquiry->status,
                'updated_by' => auth()->id(),
            ]);

            return redirect()->route('admin.inquiries.show', $inquiry)
                ->with('success', __('admin.inquiry_updated'));
        } catch (\Exception $e) {
            Log::error('Failed to update inquiry status', [
                'inquiry_id' => $inquiry->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', __('admin.inquiry_update_failed'));
        }
    }
}
