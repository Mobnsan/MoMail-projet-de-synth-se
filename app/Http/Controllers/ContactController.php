<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use OpenSpout\Reader\CSV\Reader as CSVReader;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    private function detectFields($rows)
    {
        if (empty($rows)) {
            return ['name' => '', 'email' => '', 'company' => ''];
        }

        $keys = array_keys($rows[0]);
        $normalized = [];
        foreach ($keys as $key) {
            $normalized[strtolower(trim($key))] = $key;
        }

        return [
            'name' => $normalized['name'] ?? $normalized['fullname'] ?? $normalized['first name'] ?? $normalized['firstname'] ?? ($keys[0] ?? ''),
            'email' => $normalized['email'] ?? $normalized['e_mail'] ?? $normalized['email address'] ?? $normalized['e-mail'] ?? '',
            'company' => $normalized['company'] ?? $normalized['organization'] ?? $normalized['org'] ?? $normalized['company name'] ?? '',
        ];
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'File upload is required.'], 400);
        }

        $file = $request->file('file');
        $fileName = strtolower($file->getClientOriginalName());
        $filePath = $file->getRealPath();
        $rows = [];

        try {
            if (str_ends_with($fileName, '.csv')) {
                $reader = new CSVReader();
                $reader->open($filePath);
            } else {
                $reader = new XLSXReader();
                $reader->open($filePath);
            }

            $headers = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $cells = $row->toArray();
                    if ($rowIndex === 1) {
                        $headers = array_map('trim', $cells);
                    } else {
                        $rowData = [];
                        foreach ($headers as $index => $header) {
                            $rowData[$header] = $cells[$index] ?? '';
                        }
                        $rows[] = $rowData;
                    }
                }
                break;
            }
            $reader->close();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to read file. Please choose a valid CSV or XLSX file.'], 400);
        }

        return response()->json([
            'rows' => $rows,
            'mapping' => $this->detectFields($rows)
        ]);
    }

    public function index()
    {
        $contacts = Contact::where('ownerId', Auth::id())->get();
        return response()->json($contacts);
    }

    public function store(Request $request)
    {
        $contactsData = $request->input('contacts');
        if (!is_array($contactsData)) {
            return response()->json(['error' => 'Contacts must be an array.'], 400);
        }

        $savedCount = 0;
        foreach ($contactsData as $data) {
            $extraFields = collect($data)->except(['id', 'ownerId', 'name', 'email', 'company', 'group'])->toArray();

            Contact::create([
                'id' => Str::uuid(),
                'ownerId' => Auth::id(),
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'company' => $data['company'] ?? null,
                'group' => $data['group'] ?? null,
                'extra_fields' => empty($extraFields) ? null : $extraFields,
            ]);
            $savedCount++;
        }

        return response()->json(['saved' => $savedCount]);
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::where('id', $id)->where('ownerId', Auth::id())->first();
        if (!$contact) {
            return response()->json(['error' => 'Contact not found'], 404);
        }

        $data = $request->all();
        $extraFields = collect($data)->except(['id', 'ownerId', 'name', 'email', 'company', 'group', 'created_at', 'updated_at'])->toArray();

        $contact->update([
            'name' => array_key_exists('name', $data) ? $data['name'] : $contact->name,
            'email' => array_key_exists('email', $data) ? $data['email'] : $contact->email,
            'company' => array_key_exists('company', $data) ? $data['company'] : $contact->company,
            'group' => array_key_exists('group', $data) ? $data['group'] : $contact->group,
            'extra_fields' => empty($extraFields) ? $contact->extra_fields : array_merge($contact->extra_fields ?? [], $extraFields),
        ]);

        return response()->json($contact);
    }

    public function destroy($id)
    {
        $contact = Contact::where('id', $id)->where('ownerId', Auth::id())->first();
        if ($contact) {
            $contact->delete();
        }
        return response()->json(['success' => true]);
    }
}
