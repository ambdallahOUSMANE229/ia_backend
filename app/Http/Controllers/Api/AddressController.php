<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Liste des adresses de l'utilisateur connecté.
     */
    public function index(Request $request)
    {
        $addresses = Address::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $addresses,
        ]);
    }

    /**
     * Ajouter une adresse.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'district' => 'nullable|string|max:150',
            'postal_code' => 'nullable|string|max:20',
        ]);

        $address = Address::create([
            'user_id' => $request->user()->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'country' => $validated['country'],
            'district' => $validated['district'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
        ]);

        return response()->json([
            'message' => 'Adresse ajoutée avec succès.',
            'data' => $address,
        ], 201);
    }

    /**
     * Afficher une adresse.
     */
    public function show(Request $request, string $id)
    {
        $address = Address::where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'data' => $address,
        ]);
    }

    /**
     * Modifier une adresse.
     */
    public function update(Request $request, string $id)
    {
        $address = Address::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'phone' => 'sometimes|required|string|max:30',
            'address' => 'sometimes|required|string|max:500',
            'city' => 'sometimes|required|string|max:100',
            'country' => 'sometimes|required|string|max:100',
            'district' => 'nullable|string|max:150',
            'postal_code' => 'nullable|string|max:20',
        ]);

        $address->update($validated);

        return response()->json([
            'message' => 'Adresse modifiée avec succès.',
            'data' => $address->fresh(),
        ]);
    }

    /**
     * Supprimer une adresse.
     */
    public function destroy(Request $request, string $id)
    {
        $address = Address::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $address->delete();

        return response()->json([
            'message' => 'Adresse supprimée avec succès.',
        ]);
    }

    /**
     * Définir une adresse comme adresse principale.
     */
    public function setDefault(Request $request, string $id)
    {
        $address = Address::where('user_id', $request->user()->id)
            ->findOrFail($id);

        Address::where('user_id', $request->user()->id)
            ->update([
                'is_default' => false,
            ]);

        $address->update([
            'is_default' => true,
        ]);

        return response()->json([
            'message' => 'Adresse principale définie avec succès.',
            'data' => $address->fresh(),
        ]);
    }
}
