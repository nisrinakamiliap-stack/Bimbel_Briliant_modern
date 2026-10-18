<?php

namespace App;

/**
 * Metode pembayaran manual yang dicatat oleh admin.
 */
enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case Cash = 'cash';
    case EWallet = 'e_wallet';

    /**
     * Label metode untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => 'Transfer Bank',
            self::Cash => 'Tunai',
            self::EWallet => 'E-Wallet / QRIS',
        };
    }
}
