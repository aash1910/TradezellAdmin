@extends('wallet.connect.layout')

@section('content')
    <div class="icon">✅</div>
    <h1 class="title">{{ $title }}</h1>
    <p class="message">{{ $message }}</p>
    
    <div style="margin-bottom: 24px;">
        <a href="javascript:window.close();" class="button">Close Window</a>
    </div>
    
    <div style="background: #f0fff4; border: 1px solid #9ae6b4; border-radius: 8px; padding: 16px; margin-top: 24px;">
        <h3 style="color: #22543d; margin-bottom: 8px;">What's Next?</h3>
        <ul style="text-align: left; color: #22543d; line-height: 1.6;">
            <li>You can now withdraw funds from your wallet</li>
            <li>Funds will be transferred to your connected bank account</li>
            <li>Processing time is typically 2-3 business days</li>
        </ul>
    </div>
@endsection 