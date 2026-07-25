<?php

namespace App\Console\Commands;

use App\Services\KmNotificationService;
use Illuminate\Console\Command;

class KirimPengingatTenggatKm extends Command
{
    protected $signature = 'km:kirim-pengingat-tenggat';

    protected $description = 'Mengirim notifikasi pengingat tenggat KM pada H-10, H-5, dan H-1.';

    public function handle(): int
    {
        $jumlahTerkirim = KmNotificationService::sendDeadlineReminders();

        $this->info(
            'Proses pengingat tenggat selesai. Notifikasi baru yang dibuat: '
            . $jumlahTerkirim
            . '.'
        );

        return self::SUCCESS;
    }
}
