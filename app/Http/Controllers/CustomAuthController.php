<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Cellar;
use Exception;

class CustomAuthController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function index()
     {
         return view('auth.welcome');
     }


    public function create()
    {
        return view('auth.create');
    }

    public function store(Request $request)
    {

        $customAttributes = [
            'first_name' => 'prénom',
            'last_name' => 'nom de famille',
            'password' => 'mot de passe',
        ];

        $request->validate([
            'first_name' => 'required|regex:/^[a-zA-ZÀ-ÿ\s\-]+$/|min:2', /* Accepte les lettres FR en majuscule et minuscule ainsi que les lettre EN.  */
            'last_name' => 'required|regex:/^[a-zA-ZÀ-ÿ\s\-]+$/|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [], $customAttributes);

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Cellar::create([
                'user_id' => $user->id,
                'name' => 'Cellier 1'
            ]);

            DB::commit();

            // Auth::login($user);
            return redirect(route('login'))->withSuccess('Votre compte a été créé avec succès!');
        } catch (Exception $e) {
            //throw $e -> ceci est pour nous seulement, pour debugger
            // Log::error('An error occurred: ' . $e->getMessage());
            DB::rollBack();
            return back()->withError('Une erreur est survenue. Veuillez réessayer.');
        }
    }

    /**
     * Authentification de l'utilisateur.
     *
     * @param  Request  $request  Les données de la requête.
     * @return \Illuminate\Http\Response  La réponse HTTP.
     */
    public function authentication(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');


        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            // À utiliser plus tard
             /*   $cellarInf = $user->cellars->pluck('name','id')->toArray();
            $ids = array_keys($cellarInf);
            $names = array_values($cellarInf); */

            $cellarInf = $user->cellars->map(function ($cellar) {
                return [
                    'id' => $cellar->id,
                    'name' => $cellar->name
                ];
            })->toArray();

         /*    dd($cellarInf); */
            // Récupération du premier cellier de l'utilisateur
        /*     $cellar = $user->cellars->first(); */

            // Création du tableau contenant les informations du cellier
          /*   $cellarInf = [
                'ids' => $ids,
                'names' => $names
            ];
 */
            // Stockage des informations du cellier dans la session
            session()->put('cellar_inf', $cellarInf);

            // Récupération des informations du cellier depuis la session
            

            // Redirection vers la page souhaitée après l'authentification réussie ou vers bottles par défaut
            return redirect()->intended(route('index'));
        }
        // Redirection vers la page de connexion avec un message d'erreur en cas d'échec d'authentification
        return redirect()->back()->withErrors('Courriel ou mot de passe invalide');
        // return redirect()->back()->withErrors(['loginError' => 'Courriel ou mot de passe invalide']);

    }
}
