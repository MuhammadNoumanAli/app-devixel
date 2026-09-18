@extends('layouts.master')

@section('title', 'Chat')

@push('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-chat.css') }}" />
<style>
  /* Full height chat container */
  .app-chat {
    position: relative;
    height: calc(100vh - 11.5rem) !important;
    max-height: calc(100vh - 11.5rem) !important;
    min-height: 560px;
    border-radius: 0.5rem;
    box-shadow: 0 2px 12px rgba(47, 43, 61, 0.08);
    background-color: #fff;
    overflow: hidden !important;
  }
  .app-chat .chat-app-row {
    height: 100% !important;
    max-height: 100% !important;
    margin: 0;
    overflow: hidden !important;
  }

  /* Left Sidebar Contacts */
  .app-chat .app-chat-contacts {
    height: 100% !important;
    max-height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
    background-color: #fff;
    width: 21.5rem;
    flex: 0 0 21.5rem;
    border-right: 1px solid #dbdade;
    overflow: hidden !important;
  }
  .app-chat .app-chat-contacts .sidebar-header {
    flex: 0 0 auto;
  }
  .app-chat .app-chat-contacts .sidebar-body {
    flex: 1 1 auto;
    display: flex !important;
    flex-direction: column !important;
    min-height: 0 !important;
    overflow-y: auto !important;
    height: calc(100% - 75px) !important;
  }
  .app-chat .chat-contact-list {
    padding: 0.25rem 0.5rem;
    margin-bottom: 0;
    list-style: none;
  }
  .chat-contact-list-item {
    transition: all 0.15s ease-in-out;
    border-radius: 0.375rem;
    margin-bottom: 0.25rem;
  }
  .chat-contact-list-item:hover:not(.active):not(.chat-contact-list-item-title) {
    background-color: #f8f7fa;
  }
  .chat-contact-list-item.active {
    background: linear-gradient(72.47deg, #7367f0 22.16%, rgba(115, 103, 240, 0.7) 76.47%) !important;
    box-shadow: 0 2px 6px rgba(115, 103, 240, 0.35);
  }
  .chat-contact-list-item.active .chat-contact-name,
  .chat-contact-list-item.active .chat-time {
    color: #fff !important;
  }
  .chat-contact-list-item.active .chat-last-message,
  .chat-contact-list-item.active .chat-contact-status {
    color: rgba(255, 255, 255, 0.85) !important;
  }
  .chat-contact-list-item.active .avatar {
    border: 2px solid #fff;
  }

  /* Chat History & Conversation Area */
  .app-chat .app-chat-history {
    position: relative;
    height: 100% !important;
    max-height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
    min-height: 0 !important;
    background-color: #f8f7fa;
    overflow: hidden !important;
  }
  .app-chat .chat-history-wrapper {
    height: 100% !important;
    max-height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
    min-height: 0 !important;
    overflow: hidden !important;
  }
  .app-chat .chat-history-header {
    flex: 0 0 65px;
    height: 65px;
    background-color: #fff;
    padding: 0.75rem 1.5rem;
    border-bottom: 1px solid #dbdade;
  }
  
  /* CRITICAL SCROLL FIX: Override Vuexy overflow:hidden */
  .app-chat .app-chat-history .chat-history-body {
    flex: 1 1 auto !important;
    height: calc(100% - 135px) !important;
    max-height: calc(100% - 135px) !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    padding: 1.5rem !important;
    min-height: 0 !important;
    -webkit-overflow-scrolling: touch;
  }
  
  .app-chat .chat-history-footer {
    flex: 0 0 70px;
    min-height: 70px;
    background-color: #fff;
    padding: 0.75rem 1.25rem;
    border-top: 1px solid #dbdade;
    box-shadow: 0 -2px 6px rgba(47, 43, 61, 0.04);
  }

  /* Custom Smooth Scrollbars */
  .app-chat .chat-history-body::-webkit-scrollbar,
  .app-chat .app-chat-contacts .sidebar-body::-webkit-scrollbar,
  .app-chat .app-chat-sidebar-right .sidebar-body::-webkit-scrollbar {
    width: 6px;
  }
  .app-chat .chat-history-body::-webkit-scrollbar-track,
  .app-chat .app-chat-contacts .sidebar-body::-webkit-scrollbar-track,
  .app-chat .app-chat-sidebar-right .sidebar-body::-webkit-scrollbar-track {
    background: transparent;
  }
  .app-chat .chat-history-body::-webkit-scrollbar-thumb,
  .app-chat .app-chat-contacts .sidebar-body::-webkit-scrollbar-thumb,
  .app-chat .app-chat-sidebar-right .sidebar-body::-webkit-scrollbar-thumb {
    background: rgba(115, 103, 240, 0.25);
    border-radius: 10px;
  }
  .app-chat .chat-history-body::-webkit-scrollbar-thumb:hover,
  .app-chat .app-chat-contacts .sidebar-body::-webkit-scrollbar-thumb:hover,
  .app-chat .app-chat-sidebar-right .sidebar-body::-webkit-scrollbar-thumb:hover {
    background: rgba(115, 103, 240, 0.5);
  }

  /* Bubble Styling & Read Status */
  .chat-history {
    display: flex;
    flex-direction: column;
    padding-left: 0;
    list-style: none;
    margin-bottom: 0;
  }
  .chat-history .chat-message {
    display: flex;
    width: 100%;
    margin-bottom: 1.25rem;
  }
  .chat-history .chat-message.chat-message-right {
    justify-content: flex-end;
  }
  .chat-history .chat-message:not(.chat-message-right) {
    justify-content: flex-start;
  }
  .chat-history .chat-message .chat-message-wrapper {
    max-width: 75%;
    display: flex;
    flex-direction: column;
  }
  .chat-history .chat-message:not(.chat-message-right) .chat-message-wrapper {
    align-items: flex-start;
    text-align: left;
  }
  .chat-history .chat-message.chat-message-right .chat-message-wrapper {
    align-items: flex-end;
    text-align: right;
  }
  .chat-history .chat-message .chat-message-text {
    padding: 0.6rem 1rem;
    border-radius: 0.5rem;
    display: inline-block;
    max-width: 100%;
    word-break: break-word;
    font-size: 0.9375rem;
    line-height: 1.4;
  }
  .chat-history .chat-message .chat-message-text p {
    margin-bottom: 0;
    white-space: pre-wrap;
    word-break: break-word;
    text-align: left;
  }
  .chat-history .chat-message:not(.chat-message-right) .chat-message-text {
    background-color: #fff !important;
    color: #5d596c !important;
    box-shadow: 0 2px 6px rgba(47, 43, 61, 0.08);
    border-top-left-radius: 0.15rem;
  }
  .chat-history .chat-message.chat-message-right .chat-message-text {
    background-color: #7367f0 !important;
    color: #fff !important;
    box-shadow: 0 2px 6px rgba(115, 103, 240, 0.35);
    border-top-right-radius: 0.15rem;
  }
  .chat-history .chat-message.chat-message-right .chat-message-text p {
    color: #fff !important;
  }
  .chat-history .chat-message:not(.chat-message-right) .chat-message-text p {
    color: #5d596c !important;
  }
  .chat-history .chat-message .msg-meta {
    margin-top: 0.25rem;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
  }
  .chat-history .chat-message.chat-message-right .msg-meta {
    justify-content: flex-end;
  }
  .chat-history .chat-message:not(.chat-message-right) .msg-meta {
    justify-content: flex-start;
  }
  .msg-status-icon {
    font-size: 0.95rem;
  }

  /* Right Sidebar (View Contact) */
  .app-chat .app-chat-sidebar-right {
    position: absolute;
    top: 0;
    right: -22rem;
    width: 22rem;
    height: 100% !important;
    background-color: #fff !important;
    box-shadow: -4px 0 16px rgba(47, 43, 61, 0.12);
    z-index: 50 !important;
    transition: right 0.28s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease;
    opacity: 0;
    pointer-events: none;
    border-left: 1px solid #dbdade;
    display: flex;
    flex-direction: column;
  }
  .app-chat .app-chat-sidebar-right.show {
    right: 0 !important;
    opacity: 1 !important;
    pointer-events: auto !important;
  }
  .app-chat .app-chat-sidebar-right .sidebar-header {
    flex: 0 0 auto;
    position: relative;
    border-bottom: 1px solid #f1f0f2;
  }
  .app-chat .app-chat-sidebar-right .sidebar-header .close-sidebar {
    position: absolute;
    top: 1.25rem;
    right: 1.25rem;
  }
  .app-chat .app-chat-sidebar-right .sidebar-body {
    flex: 1 1 auto;
    overflow-y: auto;
  }

  /* Dropdown Menu styling matching Vuexy screenshot exactly */
  .chat-history-header .dropdown-menu {
    z-index: 1050 !important;
    box-shadow: 0 5px 20px rgba(47, 43, 61, 0.12) !important;
    border: 1px solid #eeeef0;
    border-radius: 0.5rem;
    padding: 0.4rem 0;
    min-width: 11.5rem;
  }
  .chat-history-header .dropdown-item {
    padding: 0.6rem 1.25rem;
    font-size: 0.92rem;
    color: #5d596c;
    transition: all 0.15s ease-in-out;
  }
  .chat-history-header .dropdown-item:hover,
  .chat-history-header .dropdown-item:focus {
    background-color: #f8f7fa;
    color: #5d596c;
  }
  .chat-history-header .dropdown-item#btn-clear-chat {
    color: #7367f0 !important;
    font-weight: 500;
  }
  .chat-history-header .dropdown-item#btn-clear-chat:hover,
  .chat-history-header .dropdown-item#btn-clear-chat:focus {
    background-color: #f1f0fe !important;
    color: #7367f0 !important;
  }

  /* Header action icons */
  .chat-header-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #6f6b7d;
    cursor: pointer;
    transition: all 0.2s;
  }
  .chat-header-icon:hover {
    background-color: #f1f0f2;
    color: #7367f0;
  }
</style>
@endpush

@section('content')
<div class="app-chat card overflow-hidden">
  <div class="row g-0 chat-app-row">
    <!-- Contacts & Chats Left Sidebar -->
    <div class="col-auto app-chat-contacts app-sidebar overflow-hidden border-end" id="app-chat-contacts">
      <div class="sidebar-header pt-3 px-3 mx-1">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0 avatar avatar-online me-2">
              <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                {{ strtoupper(substr(Auth::user()->first_name ?? 'U', 0, 1)) }}
              </span>
            </div>
            <div class="overflow-hidden">
              <h6 class="mb-0 text-truncate font-weight-bold" style="max-width: 140px;">{{ Auth::user()->full_name }}</h6>
              <small class="text-muted">{{ Auth::user()->roles->pluck('name')->first() ?? 'Staff' }}</small>
            </div>
          </div>
          <!-- Start New Chat Button -->
          <button type="button" class="btn btn-sm btn-icon btn-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#newChatModal" title="Start New Chat">
            <i class="ti ti-plus ti-xs"></i>
          </button>
        </div>
      </div>
      <hr class="container-m-nx mt-3 mb-2" />
      
      <div class="sidebar-body">
        <!-- Search Input for Conversations & Contacts -->
        <div class="px-3 mb-2">
          <div class="input-group input-group-merge rounded-pill">
            <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
            <input
              type="text"
              id="chat-search-input"
              class="form-control chat-search-input"
              placeholder="Search..."
              aria-label="Search..."
            />
          </div>
        </div>

        <!-- 1. CHATS SECTION (Active conversations) -->
        <div class="chat-contact-list-item-title px-4 pt-3 pb-1" id="chats-header-title">
          <h5 class="text-primary mb-0 font-weight-bold" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.8px;">Chats</h5>
        </div>
        <ul class="list-unstyled chat-contact-list" id="chat-list">
          <li class="chat-contact-list-item chat-list-item-0 {{ $users->count() > 0 ? 'd-none' : '' }}" id="no-chats-found">
            <h6 class="text-muted mb-0 py-2 px-3 text-center small">No Chats Found</h6>
          </li>
          @foreach($users as $user)
            <li
              class="chat-contact-list-item {{ $activeUser && $activeUser->id == $user->id ? 'active' : '' }}"
              data-user-id="{{ $user->id }}"
              data-user-name="{{ strtolower($user->full_name) }}"
            >
              <a href="{{ route('chat.index', $user->id) }}" class="d-flex align-items-center text-decoration-none py-2 px-2 rounded">
                <div class="flex-shrink-0 avatar {{ $user->is_online ? 'avatar-online' : 'avatar-offline' }} me-2 user-avatar-container">
                  <span class="avatar-initial rounded-circle bg-label-{{ ['primary', 'success', 'warning', 'info', 'danger'][($user->id % 5)] }} font-weight-bold">
                    {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                  </span>
                </div>
                <div class="chat-contact-info flex-grow-1 ms-1 text-truncate">
                  <div class="d-flex justify-content-between align-items-center">
                    <h6 class="chat-contact-name text-truncate m-0">{{ $user->full_name }}</h6>
                    <small class="text-muted chat-time">
                      {{ $user->last_message ? $user->last_message->created_at->format('g:i A') : '' }}
                    </small>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mt-1">
                    <small class="text-muted chat-last-message text-truncate d-block" style="max-width: 140px;">
                      {{ $user->last_message ? ($user->last_message->body ?: 'Attachment') : 'No messages yet' }}
                    </small>
                    @if($user->unread_count > 0)
                      <span class="badge bg-danger rounded-pill badge-sm unread-badge">{{ $user->unread_count }}</span>
                    @endif
                  </div>
                </div>
              </a>
            </li>
          @endforeach
        </ul>

        <!-- 2. CONTACTS SECTION (Other company employees at the bottom) -->
        <ul class="list-unstyled chat-contact-list mb-0" id="contact-list">
          <li class="chat-contact-list-item chat-contact-list-item-title px-4 pt-3 pb-1" id="contacts-header-title">
            <h5 class="text-primary mb-0 font-weight-bold" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.8px;">Contacts</h5>
          </li>
          <li class="chat-contact-list-item contact-list-item-0 {{ $otherContacts->count() > 0 ? 'd-none' : '' }}" id="no-contacts-found">
            <h6 class="text-muted mb-0 py-2 px-3 text-center small">No Contacts Found</h6>
          </li>
          @foreach($otherContacts as $contact)
            <li
              class="chat-contact-list-item {{ $activeUser && $activeUser->id == $contact->id ? 'active' : '' }}"
              data-user-id="{{ $contact->id }}"
              data-user-name="{{ strtolower($contact->full_name) }}"
            >
              <a href="{{ route('chat.index', $contact->id) }}" class="d-flex align-items-center text-decoration-none py-2 px-2 rounded">
                <div class="flex-shrink-0 avatar {{ $contact->is_online ? 'avatar-online' : 'avatar-offline' }} me-2 user-avatar-container">
                  <span class="avatar-initial rounded-circle bg-label-{{ ['primary', 'success', 'warning', 'info', 'danger'][($contact->id % 5)] }} font-weight-bold">
                    {{ strtoupper(substr($contact->first_name ?? 'U', 0, 1)) }}
                  </span>
                </div>
                <div class="chat-contact-info flex-grow-1 ms-1 text-truncate">
                  <h6 class="chat-contact-name text-truncate m-0">{{ $contact->full_name }}</h6>
                  <p class="chat-contact-status text-muted text-truncate mb-0 small">
                    {{ $contact->roles->pluck('name')->first() ?? 'Staff Member' }}
                  </p>
                </div>
              </a>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
    <!-- /Contacts & Chats Left Sidebar -->

    <!-- Chat History & Conversation Area -->
    <div class="col app-chat-history">
      <div class="chat-history-wrapper">
        @if($activeUser)
          <!-- Chat Header -->
          <div class="chat-history-header">
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex overflow-hidden align-items-center">
                <i class="ti ti-menu-2 ti-sm cursor-pointer d-lg-none d-block me-2" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-contacts"></i>
                <div class="flex-shrink-0 avatar {{ $activeUser->is_online ? 'avatar-online' : 'avatar-offline' }} cursor-pointer" id="active-user-avatar" title="Click to view details">
                  <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                    {{ strtoupper(substr($activeUser->first_name ?? 'U', 0, 1)) }}
                  </span>
                </div>
                <div class="chat-contact-info flex-grow-1 ms-3 cursor-pointer" id="active-user-info-trigger" title="Click to view details">
                  <h6 class="m-0 text-dark font-weight-bold" id="active-user-name">{{ $activeUser->full_name }}</h6>
                  <small class="user-status text-muted" id="active-user-status">
                    @if($activeUser->is_online)
                      <span class="badge badge-dot bg-success me-1"></span>
                      <span class="status-text">{{ $activeUser->roles->pluck('name')->first() ?? 'Staff' }} • Online</span>
                    @else
                      <span class="badge badge-dot bg-secondary me-1"></span>
                      <span class="status-text">{{ $activeUser->roles->pluck('name')->first() ?? 'Staff' }} • Offline</span>
                    @endif
                  </small>
                </div>
              </div>

              <!-- Top Action Icons & 3-Dots Dropdown Menu (Matching Vuexy app-chat) -->
              <div class="d-flex align-items-center">
                <a href="javascript:void(0);" class="chat-header-icon d-sm-flex d-none me-2" id="btn-audio-call" title="Audio Call">
                  <i class="ti ti-phone-call fs-5"></i>
                </a>
                <a href="javascript:void(0);" class="chat-header-icon d-sm-flex d-none me-2" id="btn-video-call" title="Video Call">
                  <i class="ti ti-video fs-5"></i>
                </a>
                <a href="javascript:void(0);" class="chat-header-icon d-sm-flex d-none me-2" id="btn-toggle-search" title="Search in Chat">
                  <i class="ti ti-search fs-5"></i>
                </a>

                <!-- 3-Dots Dropdown Menu -->
                <div class="dropdown">
                  <a href="javascript:void(0);" class="chat-header-icon" id="chat-header-actions" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="More Options">
                    <i class="ti ti-dots-vertical fs-5"></i>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="chat-header-actions">
                    <a class="dropdown-item" href="javascript:void(0);" id="btn-view-contact">
                      <i class="ti ti-user me-2 ti-xs"></i>View Contact
                    </a>
                    <a class="dropdown-item" href="javascript:void(0);" id="btn-mute-notif">
                      <i class="ti ti-bell-off me-2 ti-xs"></i>Mute Notifications
                    </a>
                    <a class="dropdown-item" href="javascript:void(0);" id="btn-block-contact">
                      <i class="ti ti-ban me-2 ti-xs"></i>Block Contact
                    </a>
                    <a class="dropdown-item text-primary" href="javascript:void(0);" id="btn-clear-chat">
                      <i class="ti ti-trash me-2 ti-xs"></i>Clear Chat
                    </a>
                    <a class="dropdown-item" href="javascript:void(0);" id="btn-report-contact">
                      <i class="ti ti-flag me-2 ti-xs"></i>Report
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Inline Search in Conversation (Toggled by search icon) -->
          <div class="chat-search-wrapper d-none px-3 py-2 border-bottom bg-white" id="chat-inline-search-box">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light border-0"><i class="ti ti-search"></i></span>
              <input type="text" class="form-control bg-light border-0" id="chat-message-search-input" placeholder="Search in this conversation...">
              <button class="btn btn-light border-0" type="button" id="close-inline-search"><i class="ti ti-x"></i></button>
            </div>
          </div>

          <!-- Chat Body (Scrollable Messages Container) -->
          <div class="chat-history-body" id="chat-history-body">
            <ul class="chat-history" id="chat-messages-container">
              @forelse($messages as $msg)
                @php
                  $isOut = $msg->from_id == Auth::id();
                @endphp
                <li class="chat-message {{ $isOut ? 'chat-message-right' : '' }} mb-3" data-msg-id="{{ $msg->id }}">
                  @if(!$isOut)
                    <div class="user-avatar flex-shrink-0 me-3">
                      <div class="avatar avatar-sm">
                        <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                          {{ strtoupper(substr($activeUser->first_name ?? 'U', 0, 1)) }}
                        </span>
                      </div>
                    </div>
                  @endif
                  <div class="chat-message-wrapper">
                    <div class="chat-message-text">
                      @if($msg->body)<p class="mb-0">{{ $msg->body }}</p>@endif
                      @if($msg->attachment)
                        <div class="{{ $msg->body ? 'mt-2' : '' }}">
                          <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" class="badge {{ $isOut ? 'bg-white text-primary' : 'bg-label-info' }} text-decoration-none">
                            <i class="ti ti-paperclip me-1"></i> View Attachment
                          </a>
                        </div>
                      @endif
                    </div>
                    <div class="msg-meta text-muted">
                      <small class="fs-tiny text-muted">{{ $msg->created_at->format('g:i A') }}</small>
                      @if($isOut)
                        @if($msg->seen)
                          <i class="ti ti-checks text-primary ms-1 msg-status-icon" title="Read"></i>
                        @elseif($msg->delivered)
                          <i class="ti ti-checks text-muted ms-1 msg-status-icon" title="Delivered"></i>
                        @else
                          <i class="ti ti-check text-muted ms-1 msg-status-icon" title="Sent"></i>
                        @endif
                      @endif
                    </div>
                  </div>
                </li>
              @empty
                <li class="text-center text-muted my-5 empty-chat-msg">
                  <i class="ti ti-messages fs-1 mb-2 d-block text-secondary"></i>
                  No messages yet. Start the conversation with {{ $activeUser->full_name }}!
                </li>
              @endforelse
            </ul>
          </div>

          <!-- Chat Footer (Composer) -->
          <div class="chat-history-footer">
            <form class="form-send-message d-flex justify-content-between align-items-center w-100" id="chat-form" enctype="multipart/form-data">
              @csrf
              <input type="hidden" id="chat-to-id" name="to_id" value="{{ $activeUser->id }}">
              <div class="d-flex align-items-center me-2">
                <label for="attach-doc" class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-1 cursor-pointer" title="Attach File">
                  <i class="ti ti-paperclip ti-sm"></i>
                  <input type="file" id="attach-doc" name="attachment" class="d-none" />
                </label>
                <span id="attached-file-name" class="small text-muted text-truncate" style="max-width: 140px;"></span>
              </div>
              <input
                class="form-control message-input border-0 me-3 shadow-none"
                id="chat-message-input"
                name="body"
                placeholder="Type your message here..."
                autocomplete="off"
              />
              <button class="btn btn-primary d-flex align-items-center px-3" type="submit" id="chat-send-btn">
                <i class="ti ti-send me-1"></i>
                <span class="align-middle d-md-inline-block d-none">Send</span>
              </button>
            </form>
          </div>
        @else
          <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center py-5">
            <i class="ti ti-messages fs-1 text-muted mb-3" style="font-size: 4rem !important;"></i>
            <h5 class="text-muted">Select a conversation or start a new chat</h5>
            <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#newChatModal">
              <i class="ti ti-plus me-1"></i> Start New Chat
            </button>
          </div>
        @endif
      </div>
    </div>
    <!-- /Chat History -->

    @if($activeUser)
    <!-- Sidebar Right (View Contact Slideout) -->
    <div class="app-chat-sidebar-right" id="app-chat-sidebar-right">
      <div class="sidebar-header d-flex flex-column justify-content-center align-items-center flex-wrap px-4 pt-5 pb-3">
        <div class="avatar avatar-xl {{ $activeUser->is_online ? 'avatar-online' : 'avatar-offline' }}">
          <span class="avatar-initial rounded-circle bg-label-primary fs-2 font-weight-bold">
            {{ strtoupper(substr($activeUser->first_name ?? 'U', 0, 1)) }}
          </span>
        </div>
        <h5 class="mt-3 mb-0 text-dark font-weight-bold">{{ $activeUser->full_name }}</h5>
        <span class="text-muted small mt-1">{{ $activeUser->roles->pluck('name')->first() ?? 'Staff Member' }}</span>
        <i class="ti ti-x ti-sm cursor-pointer close-sidebar" id="close-sidebar-right" title="Close"></i>
      </div>
      <div class="sidebar-body px-4 pb-4">
        <div class="my-4">
          <p class="text-muted text-uppercase small font-weight-bold mb-2">About</p>
          <p class="mb-0 text-secondary" style="font-size: 0.9rem; line-height: 1.5;">
            {{ $activeUser->roles->pluck('name')->first() ?? 'Staff' }} team member at CMS Portal.
          </p>
        </div>
        <div class="my-4">
          <p class="text-muted text-uppercase small font-weight-bold mb-2">Personal Information</p>
          <ul class="list-unstyled d-grid gap-3 mt-3">
            <li class="d-flex align-items-center text-secondary">
              <i class="ti ti-mail me-2 text-primary fs-5"></i>
              <span class="text-truncate" style="font-size: 0.9rem;">{{ $activeUser->email }}</span>
            </li>
            <li class="d-flex align-items-center text-secondary">
              <i class="ti ti-phone-call me-2 text-primary fs-5"></i>
              <span style="font-size: 0.9rem;">{{ $activeUser->phone ?? '+1 (555) 019-2834' }}</span>
            </li>
            <li class="d-flex align-items-center text-secondary">
              <i class="ti ti-calendar me-2 text-primary fs-5"></i>
              <span style="font-size: 0.9rem;">Joined {{ $activeUser->created_at ? $activeUser->created_at->format('M Y') : 'Recently' }}</span>
            </li>
          </ul>
        </div>
        <div class="mt-4 pt-2 border-top">
          <p class="text-muted text-uppercase small font-weight-bold mb-3">Options</p>
          <div class="d-grid gap-2">
            <button class="btn btn-outline-danger btn-sm" id="sidebar-clear-chat-btn">
              <i class="ti ti-trash me-1"></i> Clear Chat History
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- /Sidebar Right -->
    @endif
  </div>
</div>

<!-- Start New Chat Modal -->
<div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newChatModalLabel"><i class="ti ti-message-plus me-2 text-primary"></i>Start New Chat</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <div class="input-group input-group-merge rounded-pill">
            <span class="input-group-text"><i class="ti ti-search"></i></span>
            <input type="text" id="staff-search-input" class="form-control" placeholder="Search staff members...">
          </div>
        </div>
        <ul class="list-group list-group-flush" id="staff-modal-list" style="max-height: 380px; overflow-y: auto;">
          @forelse($allStaff as $staff)
            <li class="list-group-item d-flex align-items-center justify-content-between px-0 staff-item" 
                data-name="{{ strtolower($staff->full_name) }}"
                data-role="{{ strtolower($staff->roles->pluck('name')->first() ?? '') }}">
              <div class="d-flex align-items-center">
                <div class="avatar {{ $staff->is_online ? 'avatar-online' : 'avatar-offline' }} me-3">
                  <span class="avatar-initial rounded-circle bg-label-{{ ['primary', 'success', 'warning', 'info', 'danger'][($staff->id % 5)] }} font-weight-bold">
                    {{ strtoupper(substr($staff->first_name ?? 'U', 0, 1)) }}
                  </span>
                </div>
                <div>
                  <h6 class="mb-0 text-dark font-weight-bold">{{ $staff->full_name }}</h6>
                  <small class="text-muted">
                    {{ $staff->roles->pluck('name')->first() ?? 'Staff' }} • 
                    @if($staff->is_online)
                      <span class="text-success font-weight-bold">Online</span>
                    @else
                      <span class="text-muted">Offline</span>
                    @endif
                  </small>
                </div>
              </div>
              <a href="{{ route('chat.index', $staff->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="ti ti-message me-1"></i> Message
              </a>
            </li>
          @empty
            <li class="list-group-item text-center text-muted py-3">No other staff members found</li>
          @endforelse
          <li class="list-group-item text-center text-muted py-4 d-none" id="no-staff-found">
            <i class="ti ti-user-x fs-2 d-block mb-1 text-secondary"></i>
            <span>No staff member matches your search</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection

@push('page-script')
<script>
  $(function() {
    var activeUserId = "{{ $activeUser ? $activeUser->id : '' }}";
    var activeUserName = "{{ $activeUser ? addslashes($activeUser->full_name) : '' }}";
    var chatBody = document.getElementById('chat-history-body');

    function scrollToBottom(smooth = false) {
      if (chatBody) {
        if (smooth) {
          $(chatBody).animate({ scrollTop: chatBody.scrollHeight }, 250);
        } else {
          chatBody.scrollTop = chatBody.scrollHeight;
        }
      }
    }
    scrollToBottom(false);

    // Search Left Sidebar (both Chats and Contacts)
    $('#chat-search-input').on('keyup', function() {
      var query = $(this).val().trim().toLowerCase();

      // 1. Filter Chats
      var visibleChats = 0;
      $('#chat-list li.chat-contact-list-item:not(.chat-list-item-0)').each(function() {
        var name = String($(this).data('user-name') || '');
        if (!query || name.indexOf(query) > -1) {
          $(this).removeClass('d-none');
          visibleChats++;
        } else {
          $(this).addClass('d-none');
        }
      });
      if (visibleChats === 0 && query) {
        $('#no-chats-found').removeClass('d-none');
      } else if (!query && {{ $users->count() }} > 0) {
        $('#no-chats-found').addClass('d-none');
      } else if (!query && {{ $users->count() }} === 0) {
        $('#no-chats-found').removeClass('d-none');
      }

      // 2. Filter Contacts
      var visibleContacts = 0;
      $('#contact-list li.chat-contact-list-item:not(.chat-contact-list-item-title):not(.contact-list-item-0)').each(function() {
        var name = String($(this).data('user-name') || '');
        if (!query || name.indexOf(query) > -1) {
          $(this).removeClass('d-none');
          visibleContacts++;
        } else {
          $(this).addClass('d-none');
        }
      });
      if (visibleContacts === 0 && query) {
        $('#no-contacts-found').removeClass('d-none');
      } else if (!query && {{ $otherContacts->count() }} > 0) {
        $('#no-contacts-found').addClass('d-none');
      } else if (!query && {{ $otherContacts->count() }} === 0) {
        $('#no-contacts-found').removeClass('d-none');
      }
    });

    // Filter staff in new chat modal (name or role)
    $('#staff-search-input').on('keyup', function() {
      var query = $(this).val().trim().toLowerCase();
      var visibleCount = 0;

      $('#staff-modal-list li.staff-item').each(function() {
        var name = String($(this).data('name') || '');
        var role = String($(this).data('role') || '');

        if (!query || name.indexOf(query) > -1 || role.indexOf(query) > -1) {
          $(this).removeClass('d-none');
          visibleCount++;
        } else {
          $(this).addClass('d-none');
        }
      });

      if (visibleCount === 0) {
        $('#no-staff-found').removeClass('d-none');
      } else {
        $('#no-staff-found').addClass('d-none');
      }
    });

    // Autofocus modal search on open and reset on close
    $('#newChatModal').on('shown.bs.modal', function () {
      $('#staff-search-input').trigger('focus');
    });
    $('#newChatModal').on('hidden.bs.modal', function () {
      $('#staff-search-input').val('').trigger('keyup');
    });

    // File attach feedback
    $('#attach-doc').on('change', function() {
      if (this.files && this.files[0]) {
        $('#attached-file-name').text(this.files[0].name);
      } else {
        $('#attached-file-name').text('');
      }
    });

    // Robust 3-Dots Dropdown Toggle
    $('#chat-header-actions').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var $menu = $(this).next('.dropdown-menu');
      $menu.toggleClass('show');
    });

    $(document).on('click', function(e) {
      if (!$(e.target).closest('#chat-header-actions, .dropdown-menu').length) {
        $('.chat-history-header .dropdown-menu').removeClass('show');
      }
      if (!$(e.target).closest('#app-chat-sidebar-right, #btn-view-contact, #active-user-avatar, #active-user-info-trigger').length) {
        $('#app-chat-sidebar-right').removeClass('show');
      }
    });

    $('.chat-history-header .dropdown-item').on('click', function() {
      $('.chat-history-header .dropdown-menu').removeClass('show');
    });

    // View Contact Slideout Sidebar Toggle
    $('#btn-view-contact, #active-user-avatar, #active-user-info-trigger').on('click', function(e) {
      e.preventDefault();
      $('#app-chat-sidebar-right').addClass('show');
    });
    $('#close-sidebar-right').on('click', function() {
      $('#app-chat-sidebar-right').removeClass('show');
    });

    // Inline Chat Search Toggle
    $('#btn-toggle-search').on('click', function() {
      $('#chat-inline-search-box').toggleClass('d-none');
      if (!$('#chat-inline-search-box').hasClass('d-none')) {
        $('#chat-message-search-input').trigger('focus');
      } else {
        $('#chat-message-search-input').val('').trigger('keyup');
      }
    });
    $('#close-inline-search').on('click', function() {
      $('#chat-inline-search-box').addClass('d-none');
      $('#chat-message-search-input').val('').trigger('keyup');
    });

    // Inline Message Search Filter
    $('#chat-message-search-input').on('keyup', function() {
      var query = $(this).val().trim().toLowerCase();
      $('#chat-messages-container li.chat-message').each(function() {
        var text = $(this).find('.chat-message-text').text().toLowerCase();
        if (!query || text.indexOf(query) > -1) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    });

    // Audio & Video Call Simulation
    $('#btn-audio-call').on('click', function() {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'info',
          title: 'Audio Call',
          text: 'Connecting voice call with ' + activeUserName + '...',
          showCancelButton: true,
          cancelButtonText: 'End Call',
          confirmButtonColor: '#28c76f',
          confirmButtonText: 'Calling...'
        });
      }
    });
    $('#btn-video-call').on('click', function() {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'info',
          title: 'Video Call',
          text: 'Starting video conference with ' + activeUserName + '...',
          showCancelButton: true,
          cancelButtonText: 'End Call',
          confirmButtonColor: '#7367f0',
          confirmButtonText: 'Connecting...'
        });
      }
    });

    // Mute Notifications Toggle
    $('#btn-mute-notif').on('click', function() {
      var isMuted = $(this).data('muted') === true;
      if (!isMuted) {
        $(this).data('muted', true).html('<i class="ti ti-bell me-2 ti-xs"></i>Unmute Notifications');
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'info', title: 'Notifications Muted', text: 'Alerts muted for this conversation.', timer: 1500, showConfirmButton: false });
        }
      } else {
        $(this).data('muted', false).html('<i class="ti ti-bell-off me-2 ti-xs"></i>Mute Notifications');
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'success', title: 'Notifications Unmuted', text: 'Alerts enabled for this conversation.', timer: 1500, showConfirmButton: false });
        }
      }
    });

    // Block Contact
    $('#btn-block-contact').on('click', function() {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'Block ' + activeUserName + '?',
          text: 'Blocked users will not be able to message you in chat.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ea5455',
          confirmButtonText: 'Yes, Block Contact'
        }).then(function(result) {
          if (result.isConfirmed) {
            Swal.fire({ icon: 'success', title: 'Contact Blocked', timer: 1500, showConfirmButton: false });
          }
        });
      }
    });

    // Report Contact
    $('#btn-report-contact').on('click', function() {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'Report User',
          input: 'select',
          inputOptions: {
            'spam': 'Spam or unsolicited messages',
            'harassment': 'Harassment or offensive behavior',
            'other': 'Other reason'
          },
          inputPlaceholder: 'Select a reason',
          showCancelButton: true,
          confirmButtonColor: '#7367f0',
          confirmButtonText: 'Submit Report'
        }).then(function(result) {
          if (result.isConfirmed && result.value) {
            Swal.fire({ icon: 'success', title: 'Report Submitted', text: 'Thank you for reporting.', timer: 1800, showConfirmButton: false });
          }
        });
      }
    });

    // Clear Chat Functionality (from dropdown or slideout sidebar)
    function triggerClearChat() {
      if (!activeUserId) return;

      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'Clear Chat?',
          text: 'Are you sure you want to clear all messages with ' + activeUserName + '? This cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#7367f0',
          cancelButtonColor: '#a8aaae',
          confirmButtonText: 'Yes, clear it!'
        }).then(function(result) {
          if (result.isConfirmed) {
            executeClearChat();
          }
        });
      } else {
        if (confirm('Clear all messages with ' + activeUserName + '?')) {
          executeClearChat();
        }
      }
    }

    function executeClearChat() {
      $.ajax({
        url: '/chat-api/clear/' + activeUserId,
        method: 'POST',
        data: {
          _token: '{{ csrf_token() }}'
        },
        success: function(res) {
          if (res.status === 'success') {
            $('#chat-messages-container').html(`
              <li class="text-center text-muted my-5 empty-chat-msg">
                <i class="ti ti-messages fs-1 mb-2 d-block text-secondary"></i>
                Chat cleared. Start a new conversation with ${activeUserName}!
              </li>
            `);
            // Update sidebar item
            var $activeItem = $('#chat-list li.chat-contact-list-item.active');
            if ($activeItem.length) {
              $activeItem.find('.chat-last-message').text('No messages yet');
              $activeItem.find('.chat-time').text('');
            }
            $('#app-chat-sidebar-right').removeClass('show');

            if (typeof Swal !== 'undefined') {
              Swal.fire({
                icon: 'success',
                title: 'Cleared!',
                text: 'Chat history cleared successfully.',
                timer: 1500,
                showConfirmButton: false
              });
            }
          }
        },
        error: function() {
          alert('Failed to clear chat history.');
        }
      });
    }

    $('#btn-clear-chat, #sidebar-clear-chat-btn').on('click', function(e) {
      e.preventDefault();
      triggerClearChat();
    });

    // Send Message
    $('#chat-form').on('submit', function(e) {
      e.preventDefault();
      var body = $('#chat-message-input').val().trim();
      var fileInput = document.getElementById('attach-doc');
      var hasFile = fileInput && fileInput.files.length > 0;

      if (!body && !hasFile) return;

      var formData = new FormData(this);
      $('#chat-send-btn').prop('disabled', true);

      $.ajax({
        url: "{{ route('chat.send') }}",
        method: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(res) {
          $('#chat-send-btn').prop('disabled', false);
          if (res.status === 'success') {
            $('#chat-message-input').val('');
            $('#attach-doc').val('');
            $('#attached-file-name').text('');
            $('.empty-chat-msg').remove();

            // Append outgoing message
            if ($('li.chat-message[data-msg-id="' + res.message.id + '"]').length === 0) {
              var attachHtml = res.message.attachment ? '<div class="' + (res.message.body ? 'mt-2' : '') + '"><a href="' + res.message.attachment + '" target="_blank" class="badge bg-white text-primary text-decoration-none"><i class="ti ti-paperclip me-1"></i> View Attachment</a></div>' : '';
              var safeBody = res.message.body ? $('<div>').text(res.message.body).html() : '';
              var bodyHtml = safeBody ? '<p class="mb-0">' + safeBody + '</p>' : '';
              
              // Determine tick status icon
              var checkIcon = '<i class="ti ti-check text-muted ms-1 msg-status-icon" title="Sent"></i>';
              if (res.message.seen) {
                checkIcon = '<i class="ti ti-checks text-primary ms-1 msg-status-icon" title="Read"></i>';
              } else if (res.message.delivered) {
                checkIcon = '<i class="ti ti-checks text-muted ms-1 msg-status-icon" title="Delivered"></i>';
              }

              var msgHtml = `
                <li class="chat-message chat-message-right mb-3" data-msg-id="${res.message.id}">
                  <div class="chat-message-wrapper">
                    <div class="chat-message-text">${bodyHtml}${attachHtml}</div>
                    <div class="msg-meta text-muted">
                      <small class="fs-tiny text-muted">${res.message.time}</small>
                      ${checkIcon}
                    </div>
                  </div>
                </li>
              `;
              $('#chat-messages-container').append(msgHtml);
              scrollToBottom(true);
            }

            // Update contact sidebar snippet
            var activeContact = $('li.chat-contact-list-item[data-user-id="' + activeUserId + '"]');
            if (activeContact.length) {
              activeContact.find('.chat-last-message').text(res.message.body || 'Attachment');
              activeContact.find('.chat-time').text(res.message.time);
            }
          }
        },
        error: function(xhr) {
          $('#chat-send-btn').prop('disabled', false);
          alert('Failed to send message.');
        }
      });
    });

    // Real-time polling for new messages, tick statuses, and online state every 2 seconds
    if (activeUserId) {
      function pollNewMessages() {
        $.ajax({
          url: "/chat-api/messages/" + activeUserId,
          method: "GET",
          success: function(res) {
            if (res.status === 'success' && res.messages) {
              // 1. Update target user online/offline status in header & sidebar
              if (res.user) {
                var isOnline = res.user.is_online;
                var $avatar = $('#active-user-avatar');
                var $status = $('#active-user-status');
                
                if (isOnline) {
                  $avatar.removeClass('avatar-offline').addClass('avatar-online');
                  $status.html('<span class="badge badge-dot bg-success me-1"></span><span class="status-text">' + res.user.role + ' • Online</span>');
                } else {
                  $avatar.removeClass('avatar-online').addClass('avatar-offline');
                  $status.html('<span class="badge badge-dot bg-secondary me-1"></span><span class="status-text">' + res.user.role + ' • Offline</span>');
                }

                // Update contact avatar dot in sidebar
                var $sideAvatar = $('li.chat-contact-list-item[data-user-id="' + activeUserId + '"] .user-avatar-container');
                if ($sideAvatar.length) {
                  if (isOnline) {
                    $sideAvatar.removeClass('avatar-offline').addClass('avatar-online');
                  } else {
                    $sideAvatar.removeClass('avatar-online').addClass('avatar-offline');
                  }
                }
              }

              // 2. Update status ticks for outgoing messages
              $.each(res.messages, function(i, msg) {
                if (msg.is_outgoing) {
                  var $msgEl = $('li.chat-message[data-msg-id="' + msg.id + '"]');
                  if ($msgEl.length) {
                    var $icon = $msgEl.find('.msg-status-icon');
                    if (msg.seen) {
                      // Viewed / Read -> Double Blue/Primary tick
                      if (!$icon.hasClass('text-primary')) {
                        $icon.removeClass('ti-check text-muted').addClass('ti-checks text-primary').attr('title', 'Read');
                      }
                    } else if (msg.delivered) {
                      // Delivered -> Double Grey tick
                      if ($icon.hasClass('ti-check') && !$icon.hasClass('ti-checks')) {
                        $icon.removeClass('ti-check').addClass('ti-checks text-muted').attr('title', 'Delivered');
                      }
                    }
                  }
                }
              });

              // 3. Find highest message ID currently in DOM
              var highestId = 0;
              $('#chat-messages-container li.chat-message').each(function() {
                var mid = parseInt($(this).data('msg-id'), 10);
                if (!isNaN(mid) && mid > highestId) {
                  highestId = mid;
                }
              });

              // 4. Append only new incoming messages
              var newMessages = res.messages.filter(function(m) {
                return m.id > highestId;
              });

              if (newMessages.length > 0) {
                $('.empty-chat-msg').remove();
                var container = $('#chat-messages-container');

                $.each(newMessages, function(i, msg) {
                  if ($('li.chat-message[data-msg-id="' + msg.id + '"]').length > 0) {
                    return;
                  }

                  var isOut = msg.is_outgoing;
                  var attachHtml = msg.attachment ? '<div class="' + (msg.body ? 'mt-2' : '') + '"><a href="' + msg.attachment + '" target="_blank" class="badge ' + (isOut ? 'bg-white text-primary' : 'bg-label-info') + ' text-decoration-none"><i class="ti ti-paperclip me-1"></i> View Attachment</a></div>' : '';
                  var safeBody = msg.body ? $('<div>').text(msg.body).html() : '';
                  var bodyHtml = safeBody ? '<p class="mb-0">' + safeBody + '</p>' : '';
                  var initial = (res.user.name && res.user.name.length > 0) ? res.user.name.charAt(0).toUpperCase() : 'U';
                  var avatarHtml = !isOut ? '<div class="user-avatar flex-shrink-0 me-3"><div class="avatar avatar-sm"><span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">' + initial + '</span></div></div>' : '';

                  var checkIcon = '';
                  if (isOut) {
                    if (msg.seen) {
                      checkIcon = '<i class="ti ti-checks text-primary ms-1 msg-status-icon" title="Read"></i>';
                    } else if (msg.delivered) {
                      checkIcon = '<i class="ti ti-checks text-muted ms-1 msg-status-icon" title="Delivered"></i>';
                    } else {
                      checkIcon = '<i class="ti ti-check text-muted ms-1 msg-status-icon" title="Sent"></i>';
                    }
                  }

                  var msgHtml = `
                    <li class="chat-message ${isOut ? 'chat-message-right' : ''} mb-3" data-msg-id="${msg.id}">
                      ${avatarHtml}
                      <div class="chat-message-wrapper">
                        <div class="chat-message-text">${bodyHtml}${attachHtml}</div>
                        <div class="msg-meta text-muted">
                          <small class="fs-tiny text-muted">${msg.time}</small>
                          ${checkIcon}
                        </div>
                      </div>
                    </li>
                  `;
                  container.append(msgHtml);
                });

                scrollToBottom(true);

                // Update active contact sidebar item
                var lastMsg = newMessages[newMessages.length - 1];
                var activeContact = $('li.chat-contact-list-item[data-user-id="' + activeUserId + '"]');
                if (activeContact.length && lastMsg) {
                  activeContact.find('.chat-last-message').text(lastMsg.body || 'Attachment');
                  activeContact.find('.chat-time').text(lastMsg.time);
                }
              }
            }
          }
        });
      }

      setInterval(pollNewMessages, 2000);
    }
  });
</script>
@endpush
