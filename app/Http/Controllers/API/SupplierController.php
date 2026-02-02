<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationMail;
use App\Models\User;
use App\Models\UserSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Mail;

class SupplierController extends Controller
{
    public function showForm()
    {
        return view('supplier.registration');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Step 1
            // Business Details
            'companyName' => 'required|string|max:255',
            'supplierType' => 'required|string|max:50',
            'registrationNumber' => 'required|string|max:255',
            'yearEstablished' => 'required|integer|min:1900|max:' . date('Y'),
            'website' => 'required|url|max:255',

            //Contact Person
            'firstName' => 'required|string|min:2|max:100',
            'lastName' => 'required|string|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|min:10|max:20',
            'designation' => 'required|string|max:100',            
            'secondaryContact' => 'nullable|string|max:255',

            // Address
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postalCode' => 'required|string',
            'country' => 'required|string',
            'serviceAreas' => 'required|string',
            'agreement1' => 'required|boolean',
            'agreement2' => 'required|boolean',
            'signature' => 'required|string|max:255',
            'signatureDate' => 'required|date',

            // Step 2
            'insurance' => 'required|string',
            'licenses' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'certifications' => 'required|string',
            'paymentMethod' => 'required|string|max:50',
            'bankDetails' => 'required|string',
            'currency' => 'required|string|max:10',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
            'mediaFiles.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:10240',
            'promotionalOffers' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // ✅ File Upload Handling
        $licenseFilePath = null;
        $companyLogoPath = null;
        $mediaFilesPaths = [];

        if ($request->hasFile('licenses')) {
            $licenseFilePath = $request->file('licenses')->store('suppliers/licenses', 'public');
        }

        if ($request->hasFile('logo')) {
            $companyLogoPath = $request->file('logo')->store('suppliers/logos', 'public');
        }

        if ($request->hasFile('mediaFiles')) {
            foreach ($request->file('mediaFiles') as $file) {
                $mediaFilesPaths[] = $file->store('suppliers/media', 'public');
            }
        }
        $address = trim("{$request->street}, {$request->city}, {$request->state}, {$request->postalCode}, {$request->country}");

        $password = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 10);

        try {
            $user = User::create([
                'first_name'=> $request->firstName,
                'last_name' => $request->lastName,
                'name'      => $request->firstName . ' ' . $request->lastName,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'role'      => 'Supplier',
                'password'  => Hash::make($password),
            ]);

            // $token = $user->createToken('auth_token')->plainTextToken;
            
            $data = [
                'user_id' => $user->id,
                'business_name' => $request->companyName,
                'supplier_type' => $request->supplierType,
                'business_registration_number' => $request->registrationNumber,
                'year_established' => $request->yearEstablished,
                'website_url' => $request->website,
                'designation' => $request->designation,
                'secondary_contact' => $request->secondaryContact,
                'address' => $address,
                'operating_locations' => $request->serviceAreas,
                'insurance_details' => $request->insurance,
                'license_file' => $licenseFilePath,
                'certifications' => $request->certifications,
                'payment_method' => $request->paymentMethod,
                'bank_details' => $request->bankDetails,
                'currency' => $request->currency,
                'company_logo' => $companyLogoPath,
                'service_images' => json_encode($mediaFilesPaths),
                'promotional_offers' => $request->promotionalOffers,
                'consent_info' => $request->agreement1 ? 1 : 0,
                'consent_terms' => $request->agreement2 ? 1 : 0,
                'digital_signature' => $request->signature,
                'submitted_date' => $request->signatureDate,
            ];

            // ✅ Create Supplier
            $supplier = UserSupplier::create($data);

            // Load template
            $template = fetch_email_template('supplier_registration');

            // Parse placeholders
            $placeholders = [
                'NAME'              => $user->name,
                'EMAIL'             => $user->email,
                'PHONE'             => $user->phone,
                'PASSWORD'          => $password,
                'BUSINESS_NAME'     => $request->companyName,
                'SUPPLIER_TYPE'     => $request->supplierType,
                'REGISTRATION_NUMBER' => $request->registrationNumber,
                'YEAR_ESTABLISHED'  => $request->yearEstablished,
                'WEBSITE_URL'       => $request->website,
                'DESIGNATION'       => $request->designation,
                'ADDRESS'           => $address,
                'SERVICE_AREAS'     => $request->serviceAreas,
                'INSURANCE'         => $request->insurance,
                'CERTIFICATIONS'    => $request->certifications,
                'PAYMENT_METHOD'    => $request->paymentMethod,
                'BANK_DETAILS'      => $request->bankDetails,
                'CURRENCY'          => $request->currency,
                'PROMOTIONAL_OFFERS'=> $request->promotionalOffers,
                'DATE'              => date('Y-m-d'),
                'SUPPORT_EMAIL'     => 'info@tourbeez.com',
                'APP_NAME'          => config('app.name'),
                'ADMIN_PANEL_LINK'  => config('app.site_url') .  "/admin/login",
                'YEAR'              => date('Y')
            ];

            $parsedBody = parseTemplate($template->body, $placeholders);
            $parsedSubject = parseTemplate($template->subject, $placeholders);

            // Send to user
            Mail::to($user->email)->send(new RegistrationMail($parsedSubject, $parsedBody));

            // Load Admin template && Send to admin
            $template = fetch_email_template('supplier_registration_for_admin');
            $parsedBody = parseTemplate($template->body, $placeholders);
            $parsedSubject = parseTemplate($template->subject, $placeholders);
            $toRecipient = get_setting('MAIL_FROM_ADDRESS') ?? 'info@tourbeez.com';
            $ccRecipient = 'kiran@tourbeez.com';
            Mail::to( $toRecipient )->cc($ccRecipient)->send(new RegistrationMail($parsedSubject, $parsedBody));

            return response()->json([
                'status' => true,
                'message' => 'Supplier registered successfully!',
                'data' => $supplier
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,      
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}


