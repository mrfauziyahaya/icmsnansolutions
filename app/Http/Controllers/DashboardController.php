<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get client counts by insurance company
        $insuranceCompanyData = Client::select('insurance_company as company', DB::raw('count(*) as count'))
            ->whereNotNull('insurance_company')
            ->groupBy('insurance_company')
            ->orderBy('count', 'desc')
            ->get();

        // Get client counts by category
        $categoryData = Client::select('category', DB::raw('count(*) as count'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();

        // Get premium forecast for next 12 months
        $currentMonth = Carbon::now()->startOfMonth();
        $forecastData = [];
        
        // Initialize next 12 months with zero values
        for ($i = 0; $i < 12; $i++) {
            $month = $currentMonth->copy()->addMonths($i);
            $forecastData[] = [
                'month' => $month->format('M Y'),
                'total_premium' => 0
            ];
        }

        // Get actual premium data for next 12 months
        $premiumQuery = Client::select(
            DB::raw('DATE_FORMAT(renewal_date, "%Y-%m") as month'),
            DB::raw('SUM(nettpremium) as total_premium')
        )
            ->whereNotNull('renewal_date')
            ->where('renewal_date', '>=', $currentMonth)
            ->where('renewal_date', '<', $currentMonth->copy()->addMonths(12));

        // Apply insurance company filter if selected
        if ($request->has('insurance_company') && $request->insurance_company !== 'all') {
            $premiumQuery->where('insurance_company', $request->insurance_company);
        }

        $premiumData = $premiumQuery->groupBy('month')
            ->orderBy('month')
            ->get();

        // Debug: Check the premium data!
        \Log::info('Premium Data:', $premiumData->toArray());

        // Merge actual data with forecast data
        foreach ($premiumData as $data) {
            $monthDate = Carbon::createFromFormat('Y-m', $data->month);
            $index = $currentMonth->diffInMonths($monthDate);
            if (isset($forecastData[$index])) {
                $forecastData[$index]['total_premium'] = $data->total_premium;
            }
        }

        // Debug: Check the forecast data
        \Log::info('Forecast Data:', $forecastData);

        // Get premium data by inception date
        $inceptionPremiumData = Client::select(
            DB::raw('DATE_FORMAT(inception_date, "%Y-%m") as month'),
            DB::raw('SUM(nettpremium) as total_premium')
        )
            ->whereNotNull('inception_date')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Debug: Check the inception premium data
        \Log::info('Inception Premium Data:', $inceptionPremiumData->toArray());

        // Get premium data by inception date (yearly)
        $inceptionPremiumYearlyData = Client::select(
            DB::raw('YEAR(inception_date) as year'),
            DB::raw('SUM(nettpremium) as total_premium')
        )
            ->whereNotNull('inception_date')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        // Debug: Check the yearly premium data
        \Log::info('Yearly Premium Data:', $inceptionPremiumYearlyData->toArray());

        // Get unique insurance companies for dropdown
        $insuranceCompanies = Client::select('insurance_company')
            ->whereNotNull('insurance_company')
            ->distinct()
            ->orderBy('insurance_company')
            ->pluck('insurance_company');

        return view('dashboard', compact(
            'insuranceCompanyData', 
            'categoryData', 
            'forecastData',
            'insuranceCompanies',
            'inceptionPremiumData',
            'inceptionPremiumYearlyData'
        ));
    }
} 