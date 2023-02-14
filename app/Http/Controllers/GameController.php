<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Auth;

class GameController extends Controller
{
    public function all_games()
    {
        $all_games = [];
        $games = Game::orderBy('created_at', 'desc')->get();
        
        foreach ($games as $game) {
            $player = User::where('id', $game->player)
                ->first();
            $p_game = [
                'id' => $game->id,
                'name' => $game->name,
                'status' => $game->status,
                'bet_amount' => $game->bet_amount,
                'earning' => $game->earning,
                'loss' => $game->loss,
                'player' => $player->username,
                'created_at' => $game->created_at,
            ];
            array_push($all_games, $p_game);
        }
        return response()->json([
            'games' => $all_games,
            'message' => 'success',
        ], 200);
    }

    public function new_game(Request $request)
    {
        $user = Auth::user();
        $game = Game::create([
            'name' => $request->name,
            'status' => $request->status,
            'bet_amount' => $request->amount,
            'earning' => $request->earning,
            'loss' => $request->loss,
            'player' => $user->id,
        ]);
        $wallet = Wallet::where('user_id', $user->id)->first();
        if ($request->status == 'win') {
            $wallet->update([
                'withdrawable_balance' => $wallet->withdrawable_balance + $request->earning,
                'bet' => $wallet->bet + $request->loss,
            ]);
        } else {
            $wallet->update([
                'withdrawable_balance' => $wallet->withdrawable_balance - $request->amount,
            ]);
        }
        return response()->json([
            'game' => $game,
            'wallet' => $wallet,
            'message' => 'success',
        ], 200);
    }

    public function my_games()
    {
        $user = Auth::user();
        $games = Game::where('player', $user->id)->get();
        return response()->json([
            'games' => $games,
            'message' => 'Games fetched',
        ], 200);
    }
}