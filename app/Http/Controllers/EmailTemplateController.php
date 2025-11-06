<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use Validator;
use Redirect;

class EmailTemplateController extends Controller
{
    private $rules = array();
    private $messages = array();

    public function __construct()
    {
        //$this->middleware(['permission:email_templates'])->only('index');

        $this->rules = [
            'subject'   => ['required','max:255'],
            'body'      => ['required'],
        ];

        $this->messages = [
            'subject.required'  => translate('Email subject is required'),
            'subject.max'       => translate('Max 255 characters'),
            'body.required'     => translate('Email Body is required'),
        ];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $email_templates = EmailTemplate::all();
        return view('admin.settings.email_templates.index', compact('email_templates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    public function preview(Request $request, $id)
    {
        $str='<table width="100%" bgcolor="#ecf0f1" cellpadding="0" cellspacing="0" border="0" id="background_table">
        <tbody><tr><td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; padding-top: 0px;">';
        $preview = EmailTemplate::where('identifier', $id)->first();
        if($preview) {
            $str = $preview->header;
            $str.= $preview->body;
            $str.= $preview->footer;
        }

        $str.= '</td></tr></tbody></table>';

        return $str;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $rules      = $this->rules;
        $messages   = $this->messages;
        $validator  = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            //flash(translate('Something went wrong'))->error();
            return Redirect::back()->withInput()->withErrors($validator)->with('error', translate('Something went wrong'));
        }

        $email_template             = EmailTemplate::where('identifier', $request->identifier)->first();
        $email_template->subject    = $request->subject;
        $email_template->header     = $request->header;
        $email_template->body       = $request->body;
        $email_template->footer     = $request->footer;
        $email_template->parameters = $request->parameters;
        if ($request->status == 1) {
            $email_template->status = 1;
        }
        else{
            $email_template->status = 0;
        }

        if($email_template->save()){
            //flash(translate('Email Template has been updated successfully'))->success();
            return back()->with( 'success', translate('Email Template has been updated successfully') );
        } else {
            //flash(translate('Sorry! Something went wrong.'))->error();
            return back()->with( 'error', translate('Sorry! Something went wrong.') );
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
