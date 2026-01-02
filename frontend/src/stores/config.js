import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useConfigStore = defineStore('config', () => {
    const surveyBaseUrl = ref('')
    const chatbotApiUrl = ref('')

    function fetchSurveyBaseUrl() {
        surveyBaseUrl.value = import.meta.env.VITE_SURVEY_BASE_URL || ''
    }

    function fetchChatbotApiUrl() {
        chatbotApiUrl.value = import.meta.env.VITE_CHATBOT_API_URL || ''
    }

    return {
        surveyBaseUrl,
        chatbotApiUrl,
        fetchSurveyBaseUrl,
        fetchChatbotApiUrl
    }
})
