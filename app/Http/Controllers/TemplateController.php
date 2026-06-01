<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Template;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::where('ownerId', Auth::id())->get();
        return response()->json($templates);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'subject' => 'required',
            'body' => 'required'
        ]);

        $template = Template::create([
            'id' => Str::uuid(),
            'ownerId' => Auth::id(),
            'name' => $request->name,
            'senderName' => $request->senderName ?? '',
            'senderEmail' => $request->senderEmail ?? '',
            'subject' => $request->subject,
            'body' => $request->body,
        ]);

        return response()->json($template);
    }

    public function duplicate(Request $request, $id)
    {
        $original = Template::where('id', $id)->where('ownerId', Auth::id())->first();
        
        if (!$original) {
            return response()->json(['error' => 'Template not found.'], 404);
        }

        $duplicate = $original->replicate();
        $duplicate->id = Str::uuid();
        $duplicate->name = $original->name . ' (copy)';
        $duplicate->save();

        return response()->json($duplicate);
    }

    public function destroy($id)
    {
        $template = Template::where('id', $id)->where('ownerId', Auth::id())->first();
        if ($template) {
            $template->delete();
        }
        return response()->json(['deleted' => true]);
    }
}
