@extends('wallet.connect.layout')

@section('content')
    <div class="icon">❌</div>
    <h1 class="title">{{ $title }}</h1>
    <p class="message">{{ $message }}</p>
    
    <div style="margin-bottom: 24px;">
        <a href="javascript:history.back();" class="button">Try Again</a>
    </div>
    
    <div style="background: #fed7d7; border: 1px solid #fc8181; border-radius: 8px; padding: 16px; margin-top: 24px;">
        <h3 style="color: #742a2a; margin-bottom: 8px;">Need Help?</h3>
        <ul style="text-align: left; color: #742a2a; line-height: 1.6;">
            <li>Check your internet connection</li>
            <li>Make sure you're using a supported browser</li>
            <li>Contact support if the problem persists</li>
            <li>Try again in a few minutes</li>
        </ul>
    </div>
@endsection 