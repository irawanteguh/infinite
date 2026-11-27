# Komponen bersama

Banner header modul memakai `app-geometric-banner app-module-banner` dengan tinggi minimum 180 px pada desktop/tablet dan 160 px pada ponsel. Detail cakupan dan perilaku isi ada di [standar tinggi banner](../design/foundations.md#tinggi-banner-modul).

Gunakan [panduan desain](../design/README.md) dan [Testing Page](../../app/Modules/Developer/Views/v_testingpage.php) untuk pola visual.

| Keperluan | Pemilik dan cara menemukan |
| --- | --- |
| Layout | [dashboard-light-aside.php](../../app/Views/template/dashboard-light-aside.php) untuk layout bersidebar dan [dashboard-light-blank.php](../../app/Views/template/dashboard-light-blank.php) untuk layout ringkas. [head.php](../../app/Views/template/head.php) dan [script.php](../../app/Views/template/script.php) mengatur bundle global serta aset halaman berdasarkan segmen URI. |
| Gaya dan tabel | [CSS root](../../public/assets/css/root/) memuat palet, tabel, filter, tipografi, dan progres. [filterdatatables.js](../../public/assets/js/root/filterdatatables.js) menyediakan `initDataTable(tableSelector, searchSelector, pageLength, options)` serta persiapan kartu tabel. Periksa inisialisasi otomatis sebelum menambah inisialisasi manual. |
| Modal, Select2, tanggal | [modalscroll.js](../../public/assets/js/root/modalscroll.js) menginisialisasi Select2 dalam modal ketika `shown.bs.modal`; [datepicker.js](../../public/assets/js/root/datepicker.js) meningkatkan input tanggal; [viewdocument.js](../../public/assets/js/root/viewdocument.js) mendelegasikan klik `.btn-view-document`. Modal domain disimpan di direktori modul atau [app/Views/modal](../../app/Views/modal/). |
| AJAX dan status | Modul memakai AJAX jQuery dengan kontrak lokal. [loadingmessage.js](../../public/assets/js/root/loadingmessage.js) menyediakan `showDataLoading()` yang terkait dengan request AJAX berikutnya. [Panduan pesan](../design/messages.md) merujuk pola visual. |
| Chart dan format | [monthlycharts.js](../../public/assets/js/root/monthlycharts.js) membungkus ApexCharts saat tersedia. [amchart.js](../../public/assets/js/root/amchart.js) menyediakan `renderWordCloud()`, dan [chart.js](../../public/assets/js/root/chart.js) menyediakan antara lain `renderChartStackedBar()` dan `renderUrDonut()`. [desimal.js](../../public/assets/js/root/desimal.js) menyediakan `todesimal()`. |
| Ekspor dan impor | [downloadexcel.js](../../public/assets/js/root/downloadexcel.js) menyediakan `exportToExcel()` dan `exportTableToExcel()` dengan SheetJS. [Register::export()](../../app/Modules/Risk/Controllers/Register.php) membuat CSV server-side. [ur.js](../../public/assets/js/root/ur.js) dan [bahv.js](../../public/assets/js/root/bahv.js) memproses file dan mengirim batch header/baris JSON ke handler masing-masing. |
| PDF | [QI Summaryreport](../../app/Modules/Qi/Controllers/Summaryreport.php) dan [PE Dashboard](../../app/Modules/Pe/Controllers/Dashboard.php) memakai mPDF dan view laporan masing-masing. |
| Identitas dan organisasi | [system_helper.php](../../app/Helpers/system_helper.php) menyediakan `generateuuid()`, `encodedata()`, dan `decodedata()`. [Routingsystem](../../app/Libraries/Routingsystem.php) membangun navigasi, [DepartmentModel](../../app/Modules/Organization/Models/DepartmentModel.php) menangani master unit, dan [ModuleAccess](../../app/Libraries/ModuleAccess.php) menangani grant serta menu. |

Pemuatan bundle, perilaku dropdown, dan render PDF bergantung pada layout serta aset deployment. Periksa halaman terkait pada browser ketika mengubah komponen tersebut.
