<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ClientSeeder extends Seeder
{
    private $malaysianNames = [
        'Ahmad', 'Mohd', 'Muhammad', 'Ali', 'Abu', 'Hassan', 'Ismail', 'Ibrahim', 'Yusof', 'Zainal',
        'Lim', 'Tan', 'Wong', 'Lee', 'Chan', 'Ng', 'Chong', 'Ooi', 'Teoh', 'Khoo',
        'Kumar', 'Raj', 'Muthu', 'Samy', 'Krishnan', 'Devi', 'Lingam', 'Pillay', 'Naidu', 'Muniandy'
    ];

    private $malaysianSurnames = [
        'bin Abdullah', 'bin Ahmad', 'bin Ali', 'bin Hassan', 'bin Ismail',
        'binti Abdullah', 'binti Ahmad', 'binti Ali', 'binti Hassan', 'binti Ismail',
        'A/L', 'A/P', 'S/O', 'D/O'
    ];

    private $vehicleModels = [
        'Proton Saga', 'Proton Persona', 'Proton Iriz', 'Proton X50', 'Proton X70',
        'Perodua Myvi', 'Perodua Axia', 'Perodua Bezza', 'Perodua Aruz', 'Perodua Ativa',
        'Honda City', 'Honda Civic', 'Honda HR-V', 'Honda CR-V', 'Honda Accord',
        'Toyota Vios', 'Toyota Corolla', 'Toyota Camry', 'Toyota Hilux', 'Toyota Fortuner'
    ];

    private $insuranceCompanies = [
        'AIA General Berhad', 'AIG Malaysia Insurance Berhad', 'Allianz General Insurance Company (Malaysia) Berhad',
        'Berjaya Sompo Insurance Berhad', 'Chubb Insurance Malaysia Berhad', 'Etiqa General Insurance Berhad',
        'Generali Insurance Malaysia Berhad', 'Great Eastern General Insurance (Malaysia) Berhad',
        'Liberty General Insurance Berhad', 'Lonpac Insurance Berhad', 'MSIG Insurance (Malaysia) Bhd',
        'Pacific & Orient Insurance Co. Berhad', 'Pacific Insurance Berhad', 'Progressive Insurance Berhad',
        'QBE Insurance (Malaysia) Berhad', 'RHB Insurance Berhad', 'Tokio Marine Insurance (Malaysia) Berhad',
        'Tune Insurance Malaysia Berhad', 'Zurich General Insurance Malaysia Berhad',
        'Syarikat Takaful Malaysia Am Berhad', 'Takaful Ikhlas General Berhad',
        'Zurich General Takaful Malaysia Berhad', 'Etiqa General Takaful Berhad'
    ];

    private $categories = [
        'KERETA', 'MOTOR', 'FOREIGN WORKER', 'FIRE', 'PERSONAL ACCIDENT',
        'MEDICAL CARD', 'HIBAH TAKAFUL', 'HAJI, UMRAH, & TRAVEL', 'KONTRAKTOR'
    ];

    private $states = [
        'WP Kuala Lumpur', 'WP Labuan', 'WP Putrajaya', 'Johor', 'Kedah',
        'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak',
        'Perlis', 'Penang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu'
    ];

    private $cities = [
        'Kuala Lumpur', 'Petaling Jaya', 'Shah Alam', 'Subang Jaya', 'Klang',
        'George Town', 'Ipoh', 'Johor Bahru', 'Malacca', 'Kota Kinabalu',
        'Kuching', 'Seremban', 'Kuantan', 'Alor Setar', 'Kota Bharu'
    ];

    public function run()
    {
        //for ($i = 0; $i < 50; $i++) {
        for ($i = 0; $i < 10; $i++) {
            $name = $this->generateName();
            $phone = $this->generatePhone();
            $mykad = $this->generateMyKad();
            $plate = $this->generatePlate();
            $inceptionDate = Carbon::now()->subMonths(rand(1, 12));
            $expiryDate = $inceptionDate->copy()->addYear(1)->subDay(1);
            $renewalDate = $expiryDate->copy()->addYear(1);
            $reminderDate = $expiryDate->copy()->subMonths(1);

            Client::create([
                'name' => $name,
                'phone' => $phone,
                'mykad_companyno' => $mykad,
                'category' => $this->categories[array_rand($this->categories)],
                'plate' => $plate,
                'vehicle_model' => $this->vehicleModels[array_rand($this->vehicleModels)],
                'insurance_company' => $this->insuranceCompanies[array_rand($this->insuranceCompanies)],
                'premium' => rand(500, 5000),
                'inception_date' => $inceptionDate,
                'expiry_date' => $expiryDate,
                'renewal_date' => $renewalDate,
                'reminder_date' => $reminderDate,
                'address1' => 'No. ' . rand(1, 100) . ', Jalan ' . $this->generateRandomString(5),
                'address2' => 'Taman ' . $this->generateRandomString(6),
                'city' => $this->cities[array_rand($this->cities)],
                'state' => $this->states[array_rand($this->states)],
                'postcode' => rand(50000, 59999),
                //'status' => $this->determineStatus($expiryDate),
                'status' => 'Expiring',
            ]);
        }
    }

    private function generateName()
    {
        $name = $this->malaysianNames[array_rand($this->malaysianNames)];
        $surname = $this->malaysianSurnames[array_rand($this->malaysianSurnames)];
        return $name . ' ' . $surname;
    }

    private function generatePhone()
    {
        return '01' . rand(2, 9) . '-' . rand(1000000, 9999999);
    }

    private function generateMyKad()
    {
        $year = rand(50, 99);
        $month = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
        $day = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        return $year . $month . $day . '-' . $random;
    }

    private function generatePlate()
    {
        $prefixes = ['W', 'B', 'J', 'K', 'M', 'N', 'P', 'T'];
        $prefix = $prefixes[array_rand($prefixes)];
        $numbers = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $letters = chr(rand(65, 90)) . chr(rand(65, 90));
        return $prefix . ' ' . $numbers . ' ' . $letters;
    }

    private function generateRandomString($length)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    private function determineStatus($expiryDate)
    {
        $statuses = ['ACTIVE', 'EXPIRING', 'EXPIRED'];
        return $statuses[array_rand($statuses)];
    }
} 