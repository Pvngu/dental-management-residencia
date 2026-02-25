<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receta Médica - {{ $prescription->prescription_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
            padding: 40px 50px;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .header-left {
            display: table-cell;
            vertical-align: top;
            width: 70%;
        }

        .header-right {
            display: table-cell;
            vertical-align: top;
            text-align: right;
            width: 30%;
        }

        .logo-info {
            display: table;
            width: 100%;
        }

        .rx-logo {
            display: table-cell;
            vertical-align: top;
            width: 70px;
            padding-right: 15px;
        }

        .rx-symbol {
            width: 60px;
            height: 60px;
            background-color: #9ca3af;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: bold;
            color: white;
            font-family: 'Times New Roman', serif;
        }

        .doctor-info {
            display: table-cell;
            vertical-align: top;
        }

        .doctor-name {
            font-size: 16pt;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 3px;
        }

        .doctor-details {
            font-size: 10pt;
            color: #4b5563;
            line-height: 1.6;
        }

        .doctor-details strong {
            color: #1f2937;
        }

        .date-text {
            font-size: 10pt;
            color: #4b5563;
        }

        .divider {
            border-bottom: 1px solid #e5e7eb;
            margin: 20px 0;
        }

        .patient-section {
            margin-bottom: 25px;
        }

        .patient-label {
            font-size: 11pt;
            color: #4b5563;
            display: inline;
        }

        .patient-name {
            font-size: 11pt;
            color: #1f2937;
            display: inline;
        }

        .medicines-section {
            margin-bottom: 30px;
        }

        .medicine-item {
            margin-bottom: 12px;
        }

        .medicine-name {
            font-size: 11pt;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .medicine-instructions {
            font-size: 11pt;
            color: #4b5563;
        }

        .notes-section {
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .notes-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .notes-content {
            font-size: 11pt;
            color: #4b5563;
        }

        .signature-section {
            margin-top: 80px;
            text-align: center;
        }

        .signature-line {
            width: 250px;
            border-top: 1px solid #1f2937;
            margin: 0 auto;
            padding-top: 8px;
        }

        .signature-text {
            font-size: 10pt;
            color: #4b5563;
        }

        .signature-name {
            font-size: 11pt;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <div class="logo-info">
                <div class="rx-logo">
                    <div class="rx-symbol">
                        <svg width="60" height="60" viewBox="0 0 60 60">
                            <rect width="60" height="60" fill="#9ca3af"/>
                            <text x="10" y="42" font-family="Times New Roman, serif" font-size="32" font-weight="bold" fill="white">R</text>
                            <text x="30" y="50" font-family="Times New Roman, serif" font-size="20" font-weight="bold" fill="white">x</text>
                        </svg>
                    </div>
                </div>
                <div class="doctor-info">
                    <div class="doctor-name">Dr. {{ $prescription->doctor->user->name ?? 'N/A' }}</div>
                    <div class="doctor-details">
                        @if($prescription->doctor->professional_id)
                            <strong>Ced. Prof.</strong> {{ $prescription->doctor->professional_id }}<br>
                        @endif
                        @if($prescription->doctor->specialist ?? $prescription->doctor->specialization ?? null)
                            Alta especialidad {{ $prescription->doctor->specialist ?? $prescription->doctor->specialization }}<br>
                        @endif
                        @if($company->name)
                            <strong>Clínica</strong> {{ $company->name }}@if($company->address), {{ $company->address }}@endif<br>
                        @endif
                        <strong>Teléfono:</strong> {{ $prescription->doctor->user->phone ?? $company->phone ?? 'N/A' }}<br>
                        <strong>Correo electrónico:</strong> {{ $prescription->doctor->user->email ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="header-right">
            <div class="date-text">
                {{ $prescription->prescription_date ? $prescription->prescription_date->format('d M, Y. h:i:s a') : now()->format('d M, Y. h:i:s a') }}
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Patient -->
    <div class="patient-section">
        <div style="margin-bottom: 8px;">
            <span class="patient-label"><strong>Paciente</strong> </span>
            <span class="patient-name">{{ $prescription->patient->user->name ?? $prescription->patient->name ?? 'N/A' }}</span>
        </div>
        
        @if($prescription->patient->ssn)
        <div style="margin-bottom: 5px;">
            <span class="patient-label"><strong>Identificación</strong> </span>
            <span class="patient-name">{{ $prescription->patient->ssn }}</span>
        </div>
        @endif
        
        @if($prescription->patient->user && $prescription->patient->user->age)
        <div style="margin-bottom: 5px;">
            <span class="patient-label"><strong>Edad</strong> </span>
            <span class="patient-name">{{ $prescription->patient->user->age }} años</span>
        </div>
        @endif
        
        @if($prescription->patient->allergies)
        <div style="margin-bottom: 5px;">
            <span class="patient-label"><strong>Alergias</strong> </span>
            <span class="patient-name">{{ $prescription->patient->allergies }}</span>
        </div>
        @endif
        
        @if($prescription->patient->blood_type)
        <div style="margin-bottom: 5px;">
            <span class="patient-label"><strong>Tipo de sangre</strong> </span>
            <span class="patient-name">{{ $prescription->patient->blood_type }}</span>
        </div>
        @endif
    </div>

    <div class="divider"></div>

    <!-- Medicines -->
    <div class="medicines-section">
        @foreach($prescription->prescriptionItems as $item)
            <div class="medicine-item">
                <div class="medicine-name">{{ $item->medicine->name ?? $item->medicine_name }}</div>
                <div class="medicine-instructions">
                    {{ $item->dosage }}. {{ $item->frequency }}. Por {{ $item->duration }}.
                    @if($item->instructions)
                        {{ $item->instructions }}
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Notes -->
    @if($prescription->notes)
        <div class="notes-section">
            <div class="notes-title">Otras indicaciones</div>
            <div class="notes-content">{{ $prescription->notes }}</div>
        </div>
    @endif

    <!-- Signature -->
    <div class="signature-section">
        <div class="signature-line">
            <div class="signature-name">Dr. {{ $prescription->doctor->user->name ?? 'N/A' }}</div>
            <div class="signature-text">Firma del médico</div>
            @if($prescription->doctor->professional_id)
                <div class="signature-text">Ced. Prof. {{ $prescription->doctor->professional_id }}</div>
            @endif
        </div>
    </div>
</body>
</html>
