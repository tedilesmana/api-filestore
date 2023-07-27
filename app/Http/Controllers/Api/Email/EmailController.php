<?php

namespace App\Http\Controllers\Api\Email;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EmailController extends BaseController
{
    public function emailSubmitPresensi(Request $request)
    {
        try {
            $query = "select a.presklsPertemuanKe, a.presklsPokokBahasan, a.presklsTema , d.presklsdsnJenisPertemuan, c.mkkurNamaResmi, b.klsNama, e.alpa, e.hadir, e.total
				from s_presensi_kelas a
				inner join s_kelas b on a.presklsKlsId = b.klsId
				inner join s_matakuliah_kurikulum c on b.klsMkkurId = c.mkkurId
				left join  s_presensi_kelas_dosen d on a.presklsId = d.presklsdsnPresklsId
				left join (
					select presklsdtPresklsId, sum(if(presklsdtPresklsstatusId = 1, 1,0) ) alpa, sum(if(presklsdtPresklsstatusId = 2, 1,0) ) hadir , count(presklsdtPresklsstatusId) total
						from s_presensi_kelas_detil
						where presklsdtPresklsId = '$request->klsPresId' and presklsdtMhsNiu != ''
					) e on a.presklsId = e.presklsdtPresklsId
				where a.presklsId = '$request->klsPresId'";
            $info_presensi = DB::select($query);
            $jens = $info_presensi[0]->presklsdsnJenisPertemuan == 'elearning' ? 'E-Learning' : 'Tatap Muka';

            $details = [
                'title' => 'Mail from ItSolutionStuff.com',
                'body' => 'This is for testing email using smtp',
                'content' => "Dear Bapak/Ibu " . $request->fullname
                    . "<br>"
                    . "<br>Terima kasih telah melakukan penyimpanan kehadiran kelas Anda <br> <b>" . $info_presensi[0]->mkkurNamaResmi . " " . $info_presensi[0]->klsNama . "</b>, "
                    . " pada pertemuan ke - " . $info_presensi[0]->presklsPertemuanKe . " dengan rekap kehadiran sebagai berikut :"
                    . "<br>"
                    . "<br> Tema Kelas : " . $info_presensi[0]->presklsTema
                    . "<br> Pokok Bahasan : " . $info_presensi[0]->presklsPokokBahasan
                    . "<br> Jenis Pertemuan : " . $jens
                    . "<br>"
                    . "<br> Total Peserta : " . $info_presensi[0]->total
                    . "<br> Peserta Hadir : " . $info_presensi[0]->hadir
                    . "<br> Peserta Alpha : " . $info_presensi[0]->alpa
                    . "<br>"
                    . "<br>Terima kasih atas partisipasi Anda."
                    . "<br>"
                    . "<br>Best Regards"
                    . "<br>Bagian Akademik Univeristas Paramadina"
                    . "<br>"
                    . "<br>Jl. Gatot Subroto Kav. 97 Mampang"
                    . "<br>Jakarta 12790 Indonesia"
            ];

            Mail::to('tedi.lesmana0123@gmail.com')->send(new \App\Mail\SubmitPresensi($details));

            return $this->successResponse("Email sended to tedi.lesmana0123@gmail.com", null, null);
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }
}
