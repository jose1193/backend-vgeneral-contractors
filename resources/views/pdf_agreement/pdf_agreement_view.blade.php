<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Agreement Preview</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .signatories {
            margin-top: 30px;
        }

        .signatory {
            margin-bottom: 15px;
        }

        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #333;
            width: 200px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Claim Agreement</h1>

        <div class="section">
            <p><strong>Agreement Date:</strong> {{ now()->format('F j, Y') }}</p>
        </div>

        <div class="section">
            <div class="section-title">Agreement Details</div>
            <p><strong>Claim ID:</strong> {{ $details['claim_uuid'] }}</p>
            <p><strong>Description:</strong> {{ $details['description'] ?? 'N/A' }}</p>
        </div>

        <div class="section">
            <div class="section-title">Signatories</div>

            <div class="signatory">
                <p><strong>Name:</strong> </p>
                <p><strong>Role:</strong> </p>
                <div class="signature-line"></div>
            </div>

        </div>

        <div class="section">
            <div class="section-title">Agreement Terms</div>
            <p>{{ $details['terms'] ?? 'No terms provided.' }}</p>
        </div>
    </div>
</body>

</html>
