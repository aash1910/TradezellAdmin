@extends('wallet.connect.layout')

@section('content')
    <div class="icon">⚠️</div>
    <h1 class="title">{{ $title }}</h1>
    <p class="message">{{ $message }}</p>
    
    <div style="margin-bottom: 24px;">
        <a href="javascript:history.back();" class="button">Try Again</a>
    </div>
    
    <div style="background: #fffbeb; border: 1px solid #f6e05e; border-radius: 8px; padding: 16px; margin-top: 24px;">
        <h3 style="color: #744210; margin-bottom: 8px;">Setup Requirements</h3>
        <ul style="text-align: left; color: #744210; line-height: 1.6;">
            <li>Complete all required personal information</li>
            <li>Add a valid bank account for withdrawals</li>
            <li>Verify your identity if requested</li>
            <li>Wait for Stripe to review your account</li>
        </ul>
    </div>
@endsection 