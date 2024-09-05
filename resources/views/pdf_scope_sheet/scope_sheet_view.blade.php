<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scope Sheet - V General Contractors - {{ $claim_names }}</title>

</head>
<style>
    /* Estilos generales para el PDF */
    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }

    @page {
        size: A4;
        margin: 18px;
    }

    .fixed-names {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 50px;
        text-align: right;
        font-size: 9px;
        margin-top: -20px;
        text-transform: capitalize;
    }

    /* Estilos para la enumeración de páginas */
    .page-number:before {
        content: counter(page);
    }

    /* Estilos para el título */
    .title {
        text-align: center;
        font-size: 19px;
        font-family: Arial, sans-serif;
    }

    /* Estilos para el encabezado */
    .header {
        position: fixed;
        top: 60px;
        right: 0;
        padding: 22px;
        z-index: 1000;
        font-size: 12px;
        font-weight: bold;
        font-family: Arial, sans-serif;

    }

    /* Estilos para la imagen de encabezado */
    .header-img {
        width: 100%;
        position: relative;
        top: 20px
    }

    /* Estilos para la imagen de pie de página */
    .footer-img {
        width: 100%;
    }

    /* Estilos para el pie de página */
    .footer {
        position: fixed;
        bottom: -20px;
        left: -70px;
        width: 120%;
    }

    /* Estilos para texto destacado */
    .highlight {
        color: #D6AD00;
        font-size: 19px;
        font-family: Arial, sans-serif;
        position: relative;
        line-height: 1.5;
        margin-bottom: 10px;
        padding-bottom: 10px;
        margin: 0;
    }

    .highlight::after {
        content: "";
        display: block;
        width: 100%;
        height: 2px;
        background-color: #D6AD00;
        bottom: -4px;
    }




    /* Estilos para tablas y celdas */
    table {
        width: 100%;
        border-spacing: 12px;
    }

    td {
        width: 33.33%;
    }



    /* Estilos para imágenes dentro de la presentación */
    .table-images {
        width: 99%;
        height: 310px;
        /* Aumenta la altura de las imágenes */
        object-fit: cover;
        display: block;
        margin-top: 60px;

    }


    .page-break {
        page-break-before: always;
    }


    .page-break {
        page-break-before: always;
    }
</style>



<body>

    <header>
        <img src="{{ $headerImageBase64 }}" class="header-img" alt="Header Image">
        <div class="header">
            Contractor: {{ $company_name }}<br>
            {{ $company_address }}<br>
            {{ $company_email }}<br>
            7133646240
        </div>
        <br><br>
    </header>


    <br><br><br>
    <h1 class="title">SCOPE SHEET - V GENERAL CONTRACTORS </h1>
    <main>
        <p class="highlight" style="padding-bottom: 10px;"><strong>INFO</strong></p>
        <table style="border-collapse:collapse;border:none;">
            <tbody>
                <tr>
                    <td
                        style="width: 154pt;border: 1pt solid black;background: rgb(223, 223, 223);padding: 0in;height: 11.7pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:.95pt;line-height:10.35pt;'>
                            <strong><span style='font-size:13px;font-family:"Arial","sans-serif";'>Name</span></strong>
                        </p>
                    </td>
                    <td
                        style="width: 378.9pt;border-top: 1pt solid black;border-right: 1pt solid black;border-bottom: 1pt solid black;border-image: initial;border-left: none;padding: 0in;height: 11.7pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:1.0pt;line-height:10.35pt;'>
                            <span style="font-size:13px;">&nbsp;</span><strong><span
                                    style="font-size:13px;">{{ $claim_names }}</span></strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td
                        style="width: 154pt;border-right: 1pt solid black;border-bottom: 1pt solid black;border-left: 1pt solid black;border-image: initial;border-top: none;background: rgb(223, 223, 223);padding: 0in;height: 11.7pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:.95pt;line-height:10.35pt;'>
                            <strong><span
                                    style='font-size:13px;font-family:"Arial","sans-serif";'>Address</span></strong>
                        </p>
                    </td>
                    <td
                        style="width: 378.9pt;border-top: none;border-left: none;border-bottom: 1pt solid black;border-right: 1pt solid black;padding: 0in;height: 11.7pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:1.0pt;line-height:10.35pt;'>
                            <span style="font-size:13px;">&nbsp;</span><strong><span
                                    style="font-size:13px;">{{ $property_address }}</span></strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td
                        style="width: 154pt;border-right: 1pt solid black;border-bottom: 1pt solid black;border-left: 1pt solid black;border-image: initial;border-top: none;background: rgb(223, 223, 223);padding: 0in;height: 11.85pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:.95pt;line-height:10.5pt;'>
                            <strong><span style='font-size:13px;font-family:"Arial","sans-serif";'>Phone</span></strong>
                        </p>
                    </td>
                    <td
                        style="width: 378.9pt;border-top: none;border-left: none;border-bottom: 1pt solid black;border-right: 1pt solid black;padding: 0in;height: 11.85pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:1.0pt;line-height:10.5pt;'>
                            <span style="font-size:13px;">&nbsp;</span><strong><span
                                    style="font-size:13px;">{{ $home_phone }}</span></strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td
                        style="width: 154pt;border-right: 1pt solid black;border-bottom: 1pt solid black;border-left: 1pt solid black;border-image: initial;border-top: none;background: rgb(223, 223, 223);padding: 0in;height: 11.85pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.45pt;margin-right:0in;margin-left:.95pt;line-height:10.35pt;'>
                            <strong><span
                                    style='font-size:13px;font-family:"Arial","sans-serif";'>Insurance&nbsp;Company</span></strong>
                        </p>
                    </td>
                    <td
                        style="width: 378.9pt;border-top: none;border-left: none;border-bottom: 1pt solid black;border-right: 1pt solid black;padding: 0in;height: 11.85pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.45pt;margin-right:0in;margin-left:1.0pt;line-height:10.35pt;'>
                            <span style="font-size:13px;">&nbsp;</span><strong><span
                                    style="font-size:13px;">{{ $insurance_company }}</span></strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td
                        style="width: 154pt;border-right: 1pt solid black;border-bottom: 1pt solid black;border-left: 1pt solid black;border-image: initial;border-top: none;background: rgb(223, 223, 223);padding: 0in;height: 11.85pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:.95pt;line-height:10.5pt;'>
                            <strong><span
                                    style='font-size:13px;font-family:"Arial","sans-serif";'>Policy&nbsp;Number</span></strong>
                        </p>
                    </td>
                    <td
                        style="width: 378.9pt;border-top: none;border-left: none;border-bottom: 1pt solid black;border-right: 1pt solid black;padding: 0in;height: 11.85pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:1.0pt;line-height:10.5pt;'>
                            <span style="font-size:13px;">&nbsp;</span><strong><span
                                    style="font-size:13px;">{{ $policy_number }}</span></strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td
                        style="width: 154pt;border-right: 1pt solid black;border-bottom: 1pt solid black;border-left: 1pt solid black;border-image: initial;border-top: none;background: rgb(223, 223, 223);padding: 0in;height: 11.85pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.45pt;margin-right:0in;margin-left:.95pt;line-height:10.35pt;'>
                            <strong><span
                                    style='font-size:13px;font-family:"Arial","sans-serif";'>Cause&nbsp;of&nbsp;Loss</span></strong>
                        </p>
                    </td>
                    <td
                        style="width: 378.9pt;border-top: none;border-left: none;border-bottom: 1pt solid black;border-right: 1pt solid black;padding: 0in;height: 11.85pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.45pt;margin-right:0in;margin-left:1.0pt;line-height:10.35pt;'>
                            <span style="font-size:13px;">&nbsp;<strong>{{ $cause_of_loss }}</strong></span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td
                        style="width: 154pt;border-right: 1pt solid black;border-bottom: 1pt solid black;border-left: 1pt solid black;border-image: initial;border-top: none;background: rgb(223, 223, 223);padding: 0in;height: 11.7pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:.95pt;line-height:10.35pt;'>
                            <strong><span
                                    style='font-size:13px;font-family:"Arial","sans-serif";'>Affected&nbsp;Areas</span></strong>
                        </p>
                    </td>
                    <td
                        style="width: 378.9pt;border-top: none;border-left: none;border-bottom: 1pt solid black;border-right: 1pt solid black;padding: 0in;height: 11.7pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:1.0pt;line-height:10.35pt;'>
                            <span style="font-size:13px;">&nbsp;<strong>{{ $affected_areas }}</strong></span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td
                        style="width: 154pt;border-right: 1pt solid black;border-bottom: 1pt solid black;border-left: 1pt solid black;border-image: initial;border-top: none;background: rgb(223, 223, 223);padding: 0in;height: 11.7pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:.95pt;line-height:10.35pt;'>
                            <strong><span
                                    style='font-size:13px;font-family:"Arial","sans-serif";'>Required&nbsp;Services</span></strong>
                        </p>
                    </td>
                    <td
                        style="width: 378.9pt;border-top: none;border-left: none;border-bottom: 1pt solid black;border-right: 1pt solid black;padding: 0in;height: 11.7pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:1.0pt;line-height:10.35pt;'>
                            <span style="font-size:13px;">&nbsp;<strong>{{ $requested_services }}</strong></span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td
                        style="width: 154pt;border-right: 1pt solid black;border-bottom: 1pt solid black;border-left: 1pt solid black;border-image: initial;border-top: none;background: rgb(223, 223, 223);padding: 0in;height: 11.7pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:.95pt;line-height:10.35pt;'>
                            <strong><span style='font-size:13px;font-family:"Arial","sans-serif";'>Date of
                                    Loss</span></strong>
                        </p>
                    </td>
                    <td
                        style="width: 378.9pt;border-top: none;border-left: none;border-bottom: 1pt solid black;border-right: 1pt solid black;padding: 0in;height: 11.7pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:1.0pt;line-height:10.35pt;'>
                            <span style="font-size:13px;">&nbsp;</span><strong><span
                                    style="font-size:13px;">{{ $date_of_loss }}</span></strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td
                        style="width: 154pt;border-right: 1pt solid black;border-bottom: 1pt solid black;border-left: 1pt solid black;border-image: initial;border-top: none;background: rgb(223, 223, 223);padding: 0in;height: 11.7pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:.95pt;line-height:10.35pt;'>
                            <strong><span style='font-size:13px;font-family:"Arial","sans-serif";'>Claim
                                    Number</span></strong>
                        </p>
                    </td>
                    <td
                        style="width: 378.9pt;border-top: none;border-left: none;border-bottom: 1pt solid black;border-right: 1pt solid black;padding: 0in;height: 11.7pt;vertical-align: top;">
                        <p
                            style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Arial MT","sans-serif";margin-top:.35pt;margin-right:0in;margin-left:1.0pt;line-height:10.35pt;'>
                            <span style="font-size:13px;">&nbsp;</span><strong><span
                                    style="font-size:13px;">{{ $claim_number }}</span></strong>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <br><br>
        <table>
            @php
                // Convertir el array en una colección
                $presentationImages = collect($presentation_images);

                // Encuentra la imagen con photo_type = 'front_house'
                $coverImage = $presentationImages->firstWhere('type', 'front_house');
            @endphp

            <!-- Mostrar la portada -->
            @if ($coverImage)
                <tr>
                    <td colspan="3" style="padding: 0;">
                        <img src="{{ $coverImage['path'] }}" alt="Cover Image" style="width: 100%; height: auto;">
                    </td>
                </tr>
            @endif
        </table>

        <!-- Page Break for Subsequent Images -->
        <div style="page-break-before: always;"></div>




        @php
            $totalImages = count($presentation_images);
            $imagesPerPage = 4;
            $pages = ceil($totalImages / $imagesPerPage);
        @endphp

        @for ($page = 0; $page < $pages; $page++)
            @if ($page > 0)
                <div class="page-break"></div>
            @endif
            <header>
                <img src="{{ $headerImageBase64 }}" class="header-img" alt="Header Image">
                <div class="header">
                    Contractor: {{ $company_name }}<br>
                    {{ $company_address }}<br>
                    {{ $company_email }}<br>
                    7133646240
                </div>
                <br><br>
            </header><br><br>
            <p class="highlight"><strong>PRESENTATIONS</strong></p>
            <table>
                @for ($row = 0; $row < 2; $row++)
                    <tr>
                        @for ($col = 0; $col < 2; $col++)
                            @php
                                $index = $page * $imagesPerPage + ($row * 2 + $col);
                            @endphp
                            @if ($index < $totalImages)
                                <td>
                                    <img src="{{ $presentation_images[$index]['path'] }}" alt="Presentation Image"
                                        class="table-images">
                                </td>
                            @endif
                        @endfor
                    </tr>
                @endfor
            </table>

        @endfor



        @foreach ($zone_images as $zone)
            <div class="page-break"></div>
            <br><br> <br><br>
            <p class="highlight"><strong>{{ $zone['title'] }}</strong></p>
            @php
                $totalImages = count($zone['images']);
                $imagesPerPage = 4;
                $pages = ceil($totalImages / $imagesPerPage);
            @endphp

            @for ($page = 0; $page < $pages; $page++)
                @if ($page > 0)
                    <div class="page-break"></div>
                @endif

                <!-- Encabezado o pie de página con el número de página -->
                <p class="page-header">
                    <strong>{{ $zone['title'] }} - Page {{ $page + 1 }} de {{ $pages }}</strong>
                </p>

                <table>
                    @for ($row = 0; $row < 2; $row++)
                        <tr>
                            @for ($col = 0; $col < 2; $col++)
                                @php
                                    $index = $page * $imagesPerPage + ($row * 2 + $col);
                                @endphp
                                @if ($index < $totalImages)
                                    <td>
                                        <img src="{{ $zone['images'][$index]['path'] }}"
                                            alt="{{ $zone['images'][$index]['type'] }}" class="table-images">
                                    </td>
                                @endif
                            @endfor
                        </tr>
                    @endfor
                </table>
            @endfor

            <br><br>
            <p><strong> Notes {{ $zone['title'] }}:</strong> {{ $zone['notes'] }}</p>

        @endforeach


    </main>


    <div style="page-break-before: always;"></div>
    <br><br><br><br><br>
    <table style="border-collapse:collapse;border:none;">
        <tbody>
            <tr>
                <td style="width: 275.4pt;border: 1pt solid windowtext;padding: 0in 5.4pt;vertical-align: top;">
                    <p
                        style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Calibri","sans-serif";text-align:center;'>
                        <strong><span style='font-size:19px;font-family:  "Arial","sans-serif";'>&nbsp;</span></strong>
                    </p>
                    <p
                        style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Calibri","sans-serif";text-align:center;'>
                        <strong><span style='font-size:19px;font-family:  "Arial","sans-serif";'>&nbsp;</span></strong>
                    </p>
                    <p
                        style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Calibri","sans-serif";text-align:center;'>
                        <strong><span style='font-size:21px;font-family:  "Arial","sans-serif";'><img
                                    src="{{ $seller_signature_image }}" style="width: 120px; height:60px;"
                                    alt="seller signature"></span></strong>
                        <!-- Línea Horizontal -->
                        <hr style="border: 1px solid #000; width: 50%; margin: 10px auto;">
                        <!-- Firma -->
                    </p>
                    <p
                        style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Calibri","sans-serif";text-align:center;line-height:115%;'>
                        <strong><span style='font-size:21px;line-height:115%;font-family:"Arial","sans-serif";'>Jon
                                Doe</span></strong><strong><span
                                style='font-size:16px;line-height:115%;font-family:"Arial","sans-serif";'>&nbsp;</span></strong>
                    </p>
                    <p
                        style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Calibri","sans-serif";text-align:center;line-height:150%;'>
                        <strong><span
                                style='font-size:16px;line-height:  150%;font-family:"Arial","sans-serif";'>Leader</span></strong>
                    </p>
                    <p
                        style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Calibri","sans-serif";text-align:center;line-height:150%;'>
                        <span style='font-size:19px;line-height:150%;font-family:"Arial","sans-serif";'>Date
                            <strong>{{ $date }}</strong></span>
                    </p>
                </td>
                <td
                    style="width: 275.4pt;border-top: 1pt solid windowtext;border-right: 1pt solid windowtext;border-bottom: 1pt solid windowtext;border-image: initial;border-left: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p
                        style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Calibri","sans-serif";text-align:center;'>
                        <strong><span style='font-size:19px;font-family:  "Arial","sans-serif";'>&nbsp;</span></strong>
                    </p>
                    <p
                        style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Calibri","sans-serif";text-align:center;'>
                        <strong><span style='font-size:19px;font-family:  "Arial","sans-serif";'>&nbsp;</span></strong>
                    </p>
                    <p
                        style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Calibri","sans-serif";text-align:center;'>
                        <strong><span style='font-size:21px;font-family:  "Arial","sans-serif";'> <img
                                    src="{{ $signature_image }}" style="width: 120px; height:60px;"
                                    alt="company signature"></span></strong>
                        <!-- Línea Horizontal -->
                        <hr style="border: 1px solid #000; width: 50%; margin: 10px auto;">
                        <!-- Firma -->
                    </p>
                    <p
                        style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Calibri","sans-serif";text-align:center;line-height:150%;'>
                        <strong><span
                                style='font-size:21px;line-height:150%;font-family:"Arial","sans-serif";'>{{ $signature_name }}</span></strong>
                    </p>
                    <p
                        style='margin:0in;margin-bottom:.0001pt;font-size:15px;font-family:"Calibri","sans-serif";text-align:center;line-height:150%;'>
                        <strong><span
                                style='font-size:19px;line-height:  150%;font-family:"Arial","sans-serif";'>{{ $company_name }}</span></strong>
                    </p>
                    <p
                        style='margin:0in;margin-bottom:12.0pt;font-size:15px;font-family:"Calibri","sans-serif";text-align:center;line-height:150%;'>
                        <span style='font-size:19px;line-height:150%;font-family:"Arial","sans-serif";'>Date
                            <strong>{{ $date }}</strong></span>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>

    <footer class="footer">

        <div class="fixed-names">
            Scope Sheet - {{ $claim_names }} - Page <span class="page-number"></span>
        </div>
        <img src="{{ $footerImageBase64 }}" class="footer-img" alt="Footer Image">
    </footer>
</body>

</html>
