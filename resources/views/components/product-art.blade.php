@props(['icon' => 'home'])
<svg viewBox="0 0 320 240" class="product-art" aria-hidden="true" fill="none" xmlns="http://www.w3.org/2000/svg">
<ellipse cx="160" cy="204" rx="84" ry="10" fill="currentColor" opacity=".1"/>
@switch($icon)
@case('tech')
<path d="M101 136V102a59 59 0 0 1 118 0v34" stroke="#384b44" stroke-width="17" stroke-linecap="round"/>
<path d="M103 112v64M217 112v64" stroke="#879a90" stroke-width="25" stroke-linecap="round"/>
<rect x="84" y="115" width="38" height="72" rx="17" fill="#354840"/><rect x="198" y="115" width="38" height="72" rx="17" fill="#354840"/>
<rect x="113" y="122" width="12" height="58" rx="6" fill="#b5c0b3"/><rect x="195" y="122" width="12" height="58" rx="6" fill="#b5c0b3"/>
@break
@case('home')
<path d="M103 159l-10 44M216 159l10 44" stroke="#8a5939" stroke-width="9" stroke-linecap="round"/>
<rect x="112" y="61" width="98" height="96" rx="24" fill="#c48450"/>
<rect x="93" y="136" width="137" height="34" rx="15" fill="#d39a68"/>
<path d="M91 111v41M231 111v41" stroke="#9a653e" stroke-width="13" stroke-linecap="round"/>
<rect x="120" y="74" width="83" height="62" rx="18" fill="#dba777"/>
<path d="M161 82v42" stroke="#bc8050" stroke-width="2"/>
@break
@case('fashion')
<path d="M137 70v-8a23 23 0 0 1 46 0v8" stroke="#535d3b" stroke-width="9"/>
<rect x="111" y="65" width="98" height="130" rx="27" fill="#839066"/>
<rect x="123" y="126" width="74" height="54" rx="13" fill="#626f4a"/>
<path d="M128 81h64M132 142h56" stroke="#d2d9b8" stroke-width="4"/>
<rect x="146" y="78" width="28" height="20" rx="3" fill="#d8c59b"/>
@break
@case('vehicles')
<circle cx="91" cy="161" r="41" stroke="#394b46" stroke-width="8"/><circle cx="231" cy="161" r="41" stroke="#394b46" stroke-width="8"/>
<path d="M91 161l42-66 43 66H91l54-42h65l21 42M176 161l-36-81M209 118l-14-49h23" stroke="#b37549" stroke-width="6" stroke-linejoin="round"/>
<path d="M126 80h32" stroke="#394b46" stroke-width="8" stroke-linecap="round"/>
@break
@case('books')
<g transform="rotate(-8 160 160)"><rect x="94" y="162" width="137" height="31" rx="4" fill="#8c9a74"/><path d="M103 171h123v13H103" fill="#f5ecda"/>
<rect x="86" y="128" width="137" height="30" rx="4" fill="#be7956"/><path d="M95 136h122v13H95" fill="#f5ecda"/>
<rect x="108" y="92" width="120" height="31" rx="4" fill="#5c7b76"/><path d="M117 100h106v14H117" fill="#f5ecda"/></g>
@break
@case('sports')
<path d="M87 158l110-50 58 38-113 52z" fill="#a5af93"/><ellipse cx="98" cy="161" rx="25" ry="35" transform="rotate(-27 98 161)" fill="#788964"/>
<ellipse cx="98" cy="161" rx="15" ry="23" transform="rotate(-27 98 161)" stroke="#cad0b7" stroke-width="3"/>
<path d="M185 71v29M210 71v29M177 81h41M177 91h41" stroke="#9b684a" stroke-width="12" stroke-linecap="round"/>
@break
@endswitch
</svg>