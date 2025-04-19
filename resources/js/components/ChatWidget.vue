<template>
  <Transition name="widget-fade">
    <div class="chat-widget" :class="{ 'chat-widget--open': isOpen }">
      <!-- Toggle Button -->
      <button @click="toggleChat" class="chat-toggle group">
        <div class="chat-toggle__inner">
          <div class="chat-toggle__content">
            <Transition name="icon-flip" mode="out-in">
              <svg v-if="!isOpen" key="open" class="w-8 h-8" fill="none" viewBox="0 0 24 24">
                <path d="M12 2C6.477 2 2 6.477 2 12c0 1.5.332 2.914.92 4.2l-1.1 2.4 2.4-1.1A9.96 9.96 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2Z" 
                      class="fill-blue-600 group-hover:fill-blue-700 transition-colors"/>
                <path d="M8 12h.01M12 12h.01M16 12h.01" 
                      stroke="white" 
                      stroke-width="2" 
                      stroke-linecap="round"/>
              </svg>
              <svg v-else key="close" class="w-8 h-8" fill="none" viewBox="0 0 24 24">
                <path d="M12 2C6.477 2 2 6.477 2 12c0 1.5.332 2.914.92 4.2l-1.1 2.4 2.4-1.1A9.96 9.96 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2Z" 
                      class="fill-red-600 group-hover:fill-red-700 transition-colors"/>
                <path d="M6 18L18 6M6 6l12 12" 
                      stroke="white" 
                      stroke-width="2" 
                      stroke-linecap="round"/>
              </svg>
            </Transition>
          </div>
          <div class="chat-toggle__pulse"></div>
        </div>
      </button>

      <!-- Chat Container -->
      <Transition name="widget-slide">
        <div v-if="isOpen" ref="chatContainer" class="chat-container">
          <!-- Header -->
          <div class="chat-header">
            <div class="flex items-center gap-3">
              <div class="chat-header__icon">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.017-.962L2 18l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div>
                <h2 class="chat-header__title">AI Assistant</h2>
                <p class="chat-header__subtitle">How can I help you today?</p>
              </div>
            </div>
            <button @click="closeChat" class="chat-close-btn">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Messages -->
          <div class="chat-messages" ref="messageContainer">
            <div v-if="chats.length === 0" class="chat-empty-state">
              <svg class="chat-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
              <p class="chat-empty-text">Start a conversation with your AI assistant</p>
            </div>

            <div v-for="(chat, index) in chats" :key="chat.id" class="message-group">
              <!-- User Message -->
              <div class="message-bubble user-message">
                <p class="message-text">{{ chat.message }}</p>
                <span class="message-time">{{ formatTime(chat.created_at) }}</span>
              </div>

              <!-- AI Response -->
              <div v-if="chat.response" class="message-bubble ai-message">
                <p class="message-text">{{ chat.response }}</p>
                <span class="message-time">{{ formatTime(chat.created_at) }}</span>
              </div>
            </div>

            <!-- Loading Indicator -->
            <div v-if="loading" class="typing-indicator">
              <div class="dot-flashing"></div>
            </div>
          </div>

          <!-- Input Area -->
          <div class="chat-input">
            <form @submit.prevent="sendMessage" class="relative">
              <input
                v-model="newMessage"
                type="text"
                placeholder="Type your message here..."
                :disabled="loading"
                class="chat-input-field"
                @keydown.esc="closeChat"
              />
              <button
                type="submit"
                :disabled="loading || !newMessage.trim()"
                class="chat-send-btn"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
              </button>
            </form>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ref, nextTick, onMounted, onUnmounted } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

// Types
interface ChatMessage {
  id: number;
  message: string;
  response: string | null;
  created_at: string;
  updated_at: string;
}

// State
const toast = useToast();
const isOpen = ref(false);
const chats = ref<ChatMessage[]>([]);
const newMessage = ref('');
const loading = ref(false);
const messageContainer = ref<HTMLElement | null>(null);
const chatContainer = ref<HTMLElement | null>(null);

// Handle click outside
onClickOutside(chatContainer, (event) => {
  // Check if the click was on the toggle button
  const target = event.target as HTMLElement;
  if (!target.closest('.chat-toggle')) {
    closeChat();
  }
});

// Methods
const formatTime = (timestamp: string): string => {
  return dayjs(timestamp).fromNow();
};

const closeChat = () => {
  isOpen.value = false;
};

const toggleChat = async () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    await loadHistory();
    await nextTick();
    if (messageContainer.value) {
      messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
    }
  }
};

const loadHistory = async () => {
  try {
    const response = await axios.get<ChatMessage[]>('/chat/history');
    chats.value = response.data;
  } catch (error) {
    console.error('Error loading chat history:', error);
    toast.error('Failed to load chat history');
  }
};

const sendMessage = async () => {
  const message = newMessage.value.trim();
  if (!message || loading.value) return;

  loading.value = true;

  try {
    const response = await axios.post<ChatMessage>('/chat', { message });
    chats.value.push(response.data);
    newMessage.value = '';
    
    await nextTick();
    scrollToBottom();
  } catch (error) {
    console.error('Error sending message:', error);
    toast.error('Failed to send message');
  } finally {
    loading.value = false;
  }
};

const scrollToBottom = () => {
  if (messageContainer.value) {
    messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
  }
};

// Keyboard shortcuts
const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && isOpen.value) {
    closeChat();
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
});
</script>

<style scoped>
.chat-widget {
  @apply fixed bottom-6 right-6 z-[1000];
}

.chat-toggle {
  @apply relative w-16 h-16 rounded-full transition-all duration-300;
}

.chat-toggle__inner {
  @apply w-full h-full flex items-center justify-center relative;
}

.chat-toggle__content {
  @apply relative z-10;
}

.chat-toggle__pulse {
  @apply absolute inset-0 rounded-full bg-blue-600/20 animate-ping 
         group-hover:bg-blue-600/30 transition-opacity;
  animation-duration: 2s;
}

/* Icon flip animation */
.icon-flip-enter-active {
  transition: all 0.3s ease;
  transform-origin: center;
}

.icon-flip-leave-active {
  transition: all 0.3s ease;
  transform-origin: center;
}

.icon-flip-enter-from {
  opacity: 0;
  transform: rotate(-90deg);
}

.icon-flip-leave-to {
  opacity: 0;
  transform: rotate(90deg);
}

.chat-container {
  @apply w-96 h-[600px] bg-white rounded-xl shadow-2xl flex flex-col 
         border border-gray-100 overflow-hidden;
}

.chat-header {
  @apply px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-500 
         flex items-center justify-between text-white;
}

.chat-header__icon {
  @apply p-2 bg-blue-700 rounded-lg;
}

.chat-header__title {
  @apply text-lg font-semibold leading-tight;
}

.chat-header__subtitle {
  @apply text-sm opacity-90;
}

.chat-close-btn {
  @apply p-1 hover:bg-white/10 rounded-full transition-colors duration-200;
}

.chat-messages {
  @apply flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50;
}

.chat-empty-state {
  @apply h-full flex flex-col items-center justify-center text-center p-6;
}

.chat-empty-icon {
  @apply w-16 h-16 text-gray-300 mb-4;
}

.chat-empty-text {
  @apply text-gray-500 text-sm max-w-[200px];
}

.message-group {
  @apply space-y-2;
}

.message-bubble {
  @apply max-w-[85%] p-4 rounded-2xl relative transition-all duration-200;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.user-message {
  @apply ml-auto bg-white border border-gray-200 rounded-br-none;
}

.ai-message {
  @apply mr-auto bg-blue-600 text-white rounded-bl-none;
}

.message-text {
  @apply text-sm leading-relaxed;
}

.message-time {
  @apply text-xs opacity-70 mt-2 block;
}

.user-message .message-time {
  @apply text-gray-500;
}

.ai-message .message-time {
  @apply text-blue-100;
}

.chat-input {
  @apply border-t border-gray-100 bg-white p-4;
}

.chat-input-field {
  @apply w-full pr-12 pl-4 py-3 bg-gray-50 rounded-lg border border-transparent
         focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:bg-white
         transition-all duration-200 placeholder-gray-400 text-sm;
}

.chat-send-btn {
  @apply absolute right-2 top-1/2 -translate-y-1/2 p-2 text-blue-600 
         hover:text-blue-700 disabled:opacity-50 disabled:cursor-not-allowed;
}

.typing-indicator {
  @apply flex items-center justify-start px-4 py-2;
}

.dot-flashing {
  @apply relative h-3 w-3 rounded-full bg-gray-400;
  animation: dotFlashing 1s infinite linear alternate;
  animation-delay: 0.5s;
}

.dot-flashing::before,
.dot-flashing::after {
  content: '';
  @apply absolute inline-block h-3 w-3 rounded-full bg-gray-400;
}

.dot-flashing::before {
  left: -18px;
  animation: dotFlashing 1s infinite alternate;
  animation-delay: 0s;
}

.dot-flashing::after {
  left: 18px;
  animation: dotFlashing 1s infinite alternate;
  animation-delay: 1s;
}

@keyframes dotFlashing {
  0% { opacity: 0.2; transform: translateY(0); }
  50% { opacity: 1; transform: translateY(-4px); }
  100% { opacity: 0.2; transform: translateY(0); }
}

.widget-fade-enter-active,
.widget-fade-leave-active {
  transition: opacity 0.3s ease;
}

.widget-fade-enter-from,
.widget-fade-leave-to {
  opacity: 0;
}

.widget-slide-enter-active,
.widget-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.widget-slide-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

.widget-slide-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}
</style> 