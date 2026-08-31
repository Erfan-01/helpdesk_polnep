<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Tiket Helpdesk POLNEP
    </title>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        background-color: #f3f8fc;
        font-family: Arial, Helvetica, sans-serif;
        color: #202020;
    "
>

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        background-color: #f3f8fc;
        padding: 30px 10px;
    "
>
    <tr>
        <td align="center">

            <table
                width="600"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    width: 100%;
                    max-width: 600px;
                    background-color: #ffffff;
                    border-radius: 12px;
                    overflow: hidden;
                "
            >

                <!-- HEADER -->

                <tr>
                    <td
                        style="
                            background-color: #3aa7f5;
                            padding: 24px;
                            text-align: center;
                            color: white;
                        "
                    >

                        <div
                            style="
                                font-size: 22px;
                                font-weight: bold;
                            "
                        >
                            Helpdesk POLNEP
                        </div>

                        <div
                            style="
                                font-size: 13px;
                                margin-top: 5px;
                            "
                        >
                            Politeknik Negeri Pontianak
                        </div>

                    </td>
                </tr>


                <!-- CONTENT -->

                <tr>
                    <td
                        style="
                            padding: 28px;
                        "
                    >

                        <div
                            style="
                                font-size: 16px;
                                margin-bottom: 15px;
                            "
                        >
                            Halo,
                            <strong>
                                {{ $fullName }}
                            </strong>
                        </div>


                        <div
                            style="
                                font-size: 14px;
                                line-height: 1.6;
                            "
                        >
                            Pengajuan Anda pada layanan
                            <strong>
                                {{ $serviceName }}
                            </strong>
                            telah berhasil diterima oleh
                            Helpdesk Politeknik Negeri Pontianak.
                        </div>


                        <!-- TICKET -->

                        <div
                            style="
                                background-color: #eef8ff;
                                border: 1px solid #b9e1ff;
                                border-radius: 10px;
                                padding: 20px;
                                margin-top: 24px;
                                text-align: center;
                            "
                        >

                            <div
                                style="
                                    color: #555555;
                                    font-size: 12px;
                                    margin-bottom: 7px;
                                "
                            >
                                NOMOR TIKET
                            </div>

                            <div
                                style="
                                    color: #168de2;
                                    font-size: 25px;
                                    font-weight: bold;
                                    letter-spacing: 1px;
                                "
                            >
                                {{ $requestNumber }}
                            </div>

                        </div>


                        <!-- DETAIL -->

                        <table
                            width="100%"
                            cellpadding="8"
                            cellspacing="0"
                            border="0"
                            style="
                                margin-top: 22px;
                                font-size: 13px;
                            "
                        >

                            <tr>
                                <td
                                    width="40%"
                                    style="
                                        color: #666666;
                                    "
                                >
                                    Layanan
                                </td>

                                <td>
                                    <strong>
                                        {{ $serviceName }}
                                    </strong>
                                </td>
                            </tr>

                            <tr>
                                <td
                                    style="
                                        color: #666666;
                                    "
                                >
                                    Status
                                </td>

                                <td>
                                    <strong>
                                        {{ $status }}
                                    </strong>
                                </td>
                            </tr>

                            <tr>
                                <td
                                    style="
                                        color: #666666;
                                    "
                                >
                                    Estimasi Respon
                                </td>

                                <td>
                                    <strong>
                                        {{ $estimatedResponse }}
                                    </strong>
                                </td>
                            </tr>

                        </table>


                        <div
                            style="
                                margin-top: 25px;
                                padding: 14px;
                                background-color: #fff8df;
                                border-radius: 8px;
                                font-size: 13px;
                                line-height: 1.5;
                            "
                        >
                            Simpan nomor tiket tersebut.
                            Nomor tiket akan digunakan untuk
                            melakukan pengecekan status layanan
                            pada aplikasi Helpdesk POLNEP.
                        </div>


                        <div
                            style="
                                margin-top: 28px;
                                font-size: 13px;
                                line-height: 1.6;
                            "
                        >
                            Terima kasih.
                            <br>
                            <strong>
                                Helpdesk POLNEP
                            </strong>
                        </div>

                    </td>
                </tr>


                <!-- FOOTER -->

                <tr>
                    <td
                        style="
                            background-color: #f0f9ff;
                            padding: 15px;
                            text-align: center;
                            color: #777777;
                            font-size: 11px;
                        "
                    >
                        Email ini dikirim otomatis oleh
                        Sistem Helpdesk POLNEP.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>