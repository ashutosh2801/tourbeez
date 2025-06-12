<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\SmsTemplate;
use Validator;
use Redirect;

class SmsTemplateController extends Controller
{
    public function __construct()
    {
        $this->rules = ['message' => ['required'], ];

        $this->messages = ['message.required' => translate('Sms message is required') , ];
    }
    public function index()
    {
        $sms_templates = SmsTemplate::all();
        return view('admin.settings.sms_templates.index', compact('sms_templates'));
    }
    public function update(Request $request)
    {
        $rules = $this->rules;
        $messages = $this->messages;
        $validator = Validator::make($request->all() , $rules, $messages);

        if ($validator->fails())
        {
            return Redirect::back()
                ->withInput()
                ->withErrors($validator)->with('error', translate('Something went wrong'));
        }

        $sms_templates = SmsTemplate::where('identifier', $request->identifier)
            ->first();
        $sms_templates->message = $request->message;
        $sms_templates->parameters = $request->parameters;
        if ($request->status == 1)
        {
            $sms_templates->status = 1;
        }
        else
        {
            $sms_templates->status = 0;
        }

        if ($sms_templates->save())
        {
            return back()
                ->with('success', translate('SMS Template has been updated successfully'));
        }
        else
        {
            return back()
                ->with('error', translate('Sorry! Something went wrong.'));
        }

    }
    public function preview(Request $request, $id)
    {
        $str = '<table width="100%" bgcolor="#ecf0f1" cellpadding="0" cellspacing="0" border="0" id="background_table">
        <tbody><tr><td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; padding-top: 0px;">';
        $preview = SmsTemplate::where('identifier', $id)->first();
        if ($preview)
        {

            $str .= $preview->message;

        }

        $str .= '</td></tr><tr><td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; padding-top: 0px;">';
        $str .= $preview->parameters;
        $str .='</tbody></table>';
        return $str;
    }
}

