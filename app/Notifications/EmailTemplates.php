<?php

namespace App\Notifications;

class EmailTemplates
{
    public const PRIMARY_COLOR = '#ea580c';
    public const DARK_COLOR   = '#1a1a2e';
    public const TEXT_COLOR   = '#374151';
    public const MUTED_COLOR  = '#6b7280';
    public const BORDER_COLOR = '#e5e7eb';
    public const BG_COLOR     = '#f9fafb';

    private static ?string $logoPath = null;

    public static function logoPath(): string
    {
        if (self::$logoPath === null) {
            $path = base_path('public/images/logo-text-large.png');
            self::$logoPath = file_exists($path) ? $path : '';
        }
        return self::$logoPath;
    }

    /**
     * Attach logo as inline image and return the CID reference for use in HTML.
     * Must be called within a Mail::send() closure where $message is available.
     */
    public static function attachLogo($message): string
    {
        $path = self::logoPath();
        if ($path === '' || !file_exists($path)) {
            return '';
        }

        return $message->embed($path);
    }

    public static function wrapper(string $content, string $title, string $subtitle, string $accentColor = self::PRIMARY_COLOR, string $logoCid = ''): string
    {
        if ($logoCid !== '') {
            $logoHtml = '<img src="' . $logoCid . '" alt="K2-Net" style="height:56px;max-width:220px;object-fit:contain;">';
        } else {
            $logoHtml = '<span style="font-size:22px;font-weight:700;color:white;letter-spacing:1px;">K2-NET</span>';
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:{$accentColor};border-radius:12px 12px 0 0;padding:28px 32px;text-align:center;">
                            <div style="margin-bottom:10px;">{$logoHtml}</div>
                            <h1 style="margin:0;font-size:20px;font-weight:600;color:white;">{$title}</h1>
                            <p style="margin:6px 0 0;font-size:13px;color:rgba(255,255,255,0.85);">{$subtitle}</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="background-color:#ffffff;padding:32px;border-top:3px solid {$accentColor};">
                            {$content}
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f9fafb;border-radius:0 0 12px 12px;padding:20px 32px;border-top:1px solid #e5e7eb;">
                            <p style="margin:0 0 4px;font-size:12px;color:#9ca3af;text-align:center;">
                                K2-Net — Sistem Manajemen Tagihan &amp; Pelanggan
                            </p>
                            <p style="margin:0;font-size:11px;color:#d1d5db;text-align:center;">
                                Email ini dikirim secara otomatis. Jangan membalas email ini.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    public static function greeting(string $name): string
    {
        return "<p style=\"margin:0 0 20px;font-size:14px;color:#374151;\">Halo <strong>{$name}</strong>,</p>";
    }

    public static function paragraph(string $text, bool $muted = false): string
    {
        $color = $muted ? self::MUTED_COLOR : self::TEXT_COLOR;
        return "<p style=\"margin:0 0 16px;font-size:14px;color:{$color};line-height:1.6;\">{$text}</p>";
    }

    public static function invoiceTable(array $rows, ?array $totals = null): string
    {
        $html = '<table width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;margin:20px 0;font-size:13px;">
            <thead>
                <tr style="background-color:#f3f4f6;">
                    <th style="padding:10px 12px;text-align:left;font-weight:600;color:#374151;border-bottom:2px solid #e5e7eb;">No. Tagihan</th>
                    <th style="padding:10px 12px;text-align:left;font-weight:600;color:#374151;border-bottom:2px solid #e5e7eb;">Periode</th>
                    <th style="padding:10px 12px;text-align:right;font-weight:600;color:#374151;border-bottom:2px solid #e5e7eb;">Jumlah</th>
                    <th style="padding:10px 12px;text-align:left;font-weight:600;color:#374151;border-bottom:2px solid #e5e7eb;">Jatuh Tempo</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($rows as $i => $row) {
            $bg = $i % 2 === 0 ? '#ffffff' : '#fafafa';
            $html .= "<tr style=\"background-color:{$bg};\">";
            $html .= '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;">' . e($row['invoice_number']) . '</td>';
            $html .= '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;">' . e($row['billing_period']) . '</td>';
            $html .= '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;text-align:right;font-weight:600;">' . e($row['amount']) . '</td>';
            $html .= '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;">' . e($row['due_date']) . '</td>';
            $html .= "</tr>";
        }

        if ($totals) {
            $html .= '<tr style="background-color:#fff7ed;font-weight:700;">
                <td colspan="2" style="padding:12px;border-top:2px solid ' . self::PRIMARY_COLOR . ';">TOTAL</td>
                <td style="padding:12px;border-top:2px solid ' . self::PRIMARY_COLOR . ';text-align:right;color:' . self::PRIMARY_COLOR . ';">' . e($totals['amount']) . '</td>
                <td style="padding:12px;border-top:2px solid ' . self::PRIMARY_COLOR . ';"></td>
            </tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    public static function detailTable(array $rows): string
    {
        $html = '<table width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;margin:20px 0;font-size:13px;">';

        foreach ($rows as $row) {
            $html .= '<tr>
                <td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;color:#6b7280;width:40%;">' . e($row['label']) . '</td>
                <td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;color:#111827;font-weight:500;">' . e($row['value']) . '</td>
            </tr>';
        }

        $html .= '</table>';
        return $html;
    }

    public static function ctaButton(string $url, string $label, string $color = self::PRIMARY_COLOR): string
    {
        return '<table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;">
            <tr>
                <td align="center">
                    <a href="' . e($url) . '" style="display:inline-block;background-color:' . $color . ';color:#ffffff;font-size:14px;font-weight:600;padding:14px 32px;text-decoration:none;border-radius:8px;letter-spacing:0.3px;">' . e($label) . '</a>
                </td>
            </tr>
        </table>';
    }

    public static function fallbackLink(string $url): string
    {
        return '<p style="margin:16px 0 0;font-size:12px;color:#9ca3af;text-align:center;">
            Atau salin tautan berikut ke browser:<br>
            <a href="' . e($url) . '" style="color:' . self::PRIMARY_COLOR . ';">' . e($url) . '</a>
        </p>';
    }

    public static function alert(string $message, string $type = 'info', string $color = self::PRIMARY_COLOR): string
    {
        $bg = match ($type) {
            'success' => '#dcfce7',
            'warning' => '#fef9c3',
            'danger'  => '#fee2e2',
            default   => '#eff6ff',
        };
        $border = match ($type) {
            'success' => '#86efac',
            'warning' => '#fde047',
            'danger'  => '#fca5a5',
            default   => '#93c5fd',
        };
        $icon = match ($type) {
            'success' => '&#10003;',
            'warning' => '&#9888;',
            'danger'  => '&#10005;',
            default   => '&#8505;',
        };

        return '<div style="background-color:' . $bg . ';border-left:4px solid ' . $border . ';padding:14px 16px;border-radius:0 8px 8px 0;margin:20px 0;">
            <span style="color:' . $color . ';font-weight:600;">' . $icon . '</span> ' . e($message) . '
        </div>';
    }

    public static function paymentDetailTable(array $rows): string
    {
        $html = '<table width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;margin:20px 0;font-size:13px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">';

        foreach ($rows as $i => $row) {
            $bg = $i % 2 === 0 ? '#ffffff' : '#f9fafb';
            $html .= '<tr style="background-color:' . $bg . ';">';
            $html .= '<td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;">' . e($row['label']) . '</td>';
            $html .= '<td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#111827;font-weight:500;text-align:right;">' . e($row['value']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }
}
