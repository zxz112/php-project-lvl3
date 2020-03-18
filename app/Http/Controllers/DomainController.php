<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DomainController extends Controller
{
    public function main()
    {
        $user = DB::table('domains')->get();
        return view('domains.main', compact('user'));
    }

    public function show($id)
    {
        $domain = DB::table('domains')->find($id);
        $checks = DB::table('domain_checks')->where('domain_id', $id)->orderBy('updated_at')->get();
        return view('domains.show', compact(['domain', 'checks']));
    }

    public function index()
    {
        $domains = DB::select('SELECT domains.id, domains.name, 
        (SELECT status_code FROM domain_checks WHERE domains.id = domain_checks.domain_id ORDER BY updated_at DESC LIMIT 1) 
        AS status FROM domains 
        left JOIN domain_checks ON domains.id = domain_checks.domain_id GROUP BY domains.id');
        return view('domains.index', compact('domains'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|url',
        ]);
        $domain = parse_url($request->input('domain'));
        $scheme = $domain['scheme'];
        $host = $domain['host'];
        $correctUrl = "{$scheme}://{$host}";
        $timeNow = Carbon::now()->toDateTimeString();
        $countUrl = DB::table('domains')->where('name', $correctUrl)->count();
        if ($countUrl > 0) {
            DB::table('domains')->where('name', $correctUrl)->update(['updated_at' => $timeNow]);
            flash('Url already exists ')->success();
        } else {
            DB::table('domains')->insert(['name' => $correctUrl, 'created_at' => $timeNow, 'updated_at' => $timeNow]);
            flash('Url already added ')->success();
        }
        return redirect()->route('index');
    }

    public function check(Request $request)
    {
        $idDomain = $request->id;
        $domain = DB::table('domains')->find($idDomain);
        $domainName = $domain->name;
        $client = new Client();
        try {
            $res = $client->request('GET', $domainName);
        } catch (RequestException $e) {
            flash('Error')->error();
            return redirect()->route('show', $idDomain);
        }
        $statusCode = $res->getStatusCode();
        $timeNow = Carbon::now()->toDateTimeString();
        DB::table('domain_checks')->insert(['domain_id' => $idDomain, 'status_code' => $statusCode, 'created_at' => $timeNow, 'h1' => '', 'description' => '', 'updated_at' => $timeNow]);
        flash('Url was checked ')->success();
        return redirect()->route('show', $idDomain);
    }
}
