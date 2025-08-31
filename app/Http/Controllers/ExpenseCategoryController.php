<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    // List all categories
    public function index()
    {
        $categories = ExpenseCategory::latest()->simplePaginate(15);
        return view('expense_categories.index', compact('categories'));
    }

    // Show form to create new category
    public function create()
    {
        return view('expense_categories.create');
    }

    // Store new category
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:expense_categories,name|max:255',
        ]);

        ExpenseCategory::create([
            'name' => $request->name,
        ]);

        return redirect()->route('expense-categories.index')->with('success', 'Category added successfully.');
    }

    // Show edit form
    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expense_categories.edit', compact('expenseCategory'));
    }

    // Update category
    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id,
        ]);

        $expenseCategory->update([
            'name' => $request->name,
        ]);

        return redirect()->route('expense-categories.index')->with('success', 'Category updated successfully.');
    }

    // Delete category
    public function destroy(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->delete();

        return redirect()->route('expense-categories.index')->with('success', 'Category deleted successfully.');
    }
}
