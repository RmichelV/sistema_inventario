<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();

        // Transforma la colección de clientes para el formato esperado
        $customersFormatted = collect($customers)->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'address' => $customer->address,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'notes' => $customer->notes,
            ];
        });

        return Inertia::render('Customers/Index', [
            'customers' => $customersFormatted,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Customers/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $validated = $request->validated();

        try {
            Customer::create($validated);
            return redirect()->route('rcustomers.index')->with('success', '¡Cliente registrado exitosamente!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error al crear cliente: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return Inertia::render('Customers/edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $validated = $request->validated();

        try {
            $customer->update($validated);
            return redirect()->route('rcustomers.index')->with('success', '¡Cliente actualizado exitosamente!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error al actualizar cliente: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return redirect()->route('rcustomers.index')->with('success', 'Cliente eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar cliente.');
        }
    }
}
