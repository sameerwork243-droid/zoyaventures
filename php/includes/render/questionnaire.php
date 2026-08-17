<?php
// questionnaire.php — Questionnaire module port (src/components/modules.tsx)
// Static banner + DreamHomeQuiz trigger button. The quiz modal + wizard run
// client-side (dream-home-quiz.js, later JS phase).

require_once __DIR__ . '/rich.php';

function questionnaire(array $m): string
{
    $title = $m['title'] ?? '';
    $body = $m['content']['data']['content'] ?? null;
    $body1 = $m['content1']['data']['content1'] ?? null;

    $html = '<div class="qes-bk"><div class="container"><div class="question-banner-wrap"><div class=""><div class="question-banner-container">';
    $html .= '<div class="bg-img"><img loading="lazy" draggable="false" src="https://d3h330vgpwpjr8.cloudfront.net/x/640x700/pro_quiz_banner_a8c3cbc202.webp" srcset="https://d3h330vgpwpjr8.cloudfront.net/x/320x260/pro_quiz_banner_a8c3cbc202.webp 320w, https://d3h330vgpwpjr8.cloudfront.net/x/640x700/pro_quiz_banner_a8c3cbc202.webp 640w" sizes="(max-width: 480px) 320px, (min-width: 481px) 640px" alt="Confused About Where to Buy or Invest in Dubai? - Zoya Ventures Real Estate"></div>';
    $html .= '<div class="content-section"><div class="div-pad">';
    $html .= '<div class="content"><p class="title">' . esc($title) . '</p><div class="description">' . rich($body) . '</div></div>';
    $html .= '<div class="cta-section">';
    $html .= '<button type="button" class="button button-orange cursur" data-dream-home-quiz-open>Find My Dream Home!</button>';
    $html .= '<div class="help-info">'
        . '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 6V12H16.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="url(#paint0_linear_9303_7430)" stroke-linecap="round" stroke-linejoin="round"></path><defs><linearGradient id="paint0_linear_9303_7430" x1="12" y1="3" x2="12" y2="21" gradientUnits="userSpaceOnUse"><stop stop-color="#07224B"></stop><stop offset="1" stop-color="#EA6C2E"></stop></linearGradient></defs></svg>'
        . 'It takes only 30 seconds</div>';
    $html .= '</div>';
    $html .= '<div class="content"><div class="description">' . rich($body1) . '</div></div>';
    $html .= '</div></div></div></div></div></div>';
    return $html;
}