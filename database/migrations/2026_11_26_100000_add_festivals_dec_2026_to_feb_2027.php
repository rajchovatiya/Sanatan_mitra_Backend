<?php

use App\Models\Festival;
use Illuminate\Database\Migrations\Migration;

/**
 * One-time content: festivals and vrats from December 2026 to February 2027,
 * so the app's "Upcoming festival" shows real entries. Dates are the udaya
 * tithi at Bhavnagar from the app's own Panchang engine (Purnima falling
 * between two sunrises is kept on the day it begins). Safe to run again: a
 * festival with the same date and English name is skipped. Edit or add more
 * in Admin → Festivals.
 */
return new class extends Migration
{
    private function festivals(): array
    {
        $ekadashiAbout = [
            'en' => 'A sacred Ekadashi fast dedicated to Bhagwan Vishnu. Devotees fast, chant his name and break the fast on Dwadashi.',
            'hi' => 'भगवान विष्णु को समर्पित पवित्र एकादशी व्रत। भक्त उपवास रखते हैं, नाम जप करते हैं और द्वादशी को व्रत खोलते हैं।',
            'gu' => 'ભગવાન વિષ્ણુને સમર્પિત પવિત્ર એકાદશી વ્રત. ભક્તો ઉપવાસ રાખે છે, નામ જપ કરે છે અને દ્વાદશીએ વ્રત ખોલે છે.',
        ];
        $ekadashiRituals = [
            'en' => "Take a bath early and take the vrat sankalp\nWorship Bhagwan Vishnu with tulsi leaves\nChant Om Namo Bhagavate Vasudevaya\nBreak the fast on Dwadashi morning",
            'hi' => "प्रातः स्नान कर व्रत का संकल्प लें\nतुलसी दल से भगवान विष्णु की पूजा करें\nॐ नमो भगवते वासुदेवाय का जप करें\nद्वादशी की सुबह व्रत का पारण करें",
            'gu' => "સવારે સ્નાન કરી વ્રતનો સંકલ્પ લો\nતુલસી પાનથી ભગવાન વિષ્ણુની પૂજા કરો\nૐ નમો ભગવતે વાસુદેવાય નો જપ કરો\nદ્વાદશીની સવારે વ્રતનું પારણું કરો",
        ];
        $purnimaRituals = [
            'en' => "Take a holy dip or bath at sunrise\nOffer water to the Sun and worship Bhagwan Vishnu\nGive food and warm clothes in charity",
            'hi' => "सूर्योदय पर पवित्र स्नान करें\nसूर्य को अर्घ्य दें और भगवान विष्णु की पूजा करें\nअन्न और गर्म वस्त्र का दान करें",
            'gu' => "સૂર્યોદય સમયે પવિત્ર સ્નાન કરો\nસૂર્યને અર્ઘ્ય આપો અને ભગવાન વિષ્ણુની પૂજા કરો\nઅન્ન અને ગરમ વસ્ત્રનું દાન કરો",
        ];

        return [
            [
                'date' => '2026-12-04', 'is_vrat' => true,
                'name' => ['en' => 'Utpanna Ekadashi', 'hi' => 'उत्पन्ना एकादशी', 'gu' => 'ઉત્પન્ના એકાદશી'],
                'tithi' => ['en' => 'Margashirsha Krishna Ekadashi', 'hi' => 'मार्गशीर्ष कृष्ण एकादशी', 'gu' => 'કારતક વદ અગિયારસ'],
                'about' => [
                    'en' => 'The day Devi Ekadashi appeared from Bhagwan Vishnu. Devotees who wish to begin the Ekadashi vrat start from this day.',
                    'hi' => 'इस दिन भगवान विष्णु से देवी एकादशी प्रकट हुई थीं। एकादशी व्रत आरंभ करने वाले भक्त इसी दिन से शुरुआत करते हैं।',
                    'gu' => 'આ દિવસે ભગવાન વિષ્ણુમાંથી દેવી એકાદશી પ્રગટ થયાં હતાં. એકાદશી વ્રત શરૂ કરનાર ભક્તો આ દિવસથી શરૂઆત કરે છે.',
                ],
                'rituals' => $ekadashiRituals,
            ],
            [
                'date' => '2026-12-14', 'is_vrat' => false,
                'name' => ['en' => 'Vivah Panchami', 'hi' => 'विवाह पंचमी', 'gu' => 'વિવાહ પંચમી'],
                'tithi' => ['en' => 'Margashirsha Shukla Panchami', 'hi' => 'मार्गशीर्ष शुक्ल पंचमी', 'gu' => 'માગશર સુદ પાંચમ'],
                'about' => [
                    'en' => 'The wedding day of Bhagwan Shri Ram and Mata Sita. Ram–Sita vivah is celebrated in temples with bhajans and Ramcharitmanas path.',
                    'hi' => 'भगवान श्रीराम और माता सीता के विवाह का दिन। मंदिरों में भजन और रामचरितमानस पाठ के साथ राम–सीता विवाह मनाया जाता है।',
                    'gu' => 'ભગવાન શ્રીરામ અને માતા સીતાના વિવાહનો દિવસ. મંદિરોમાં ભજન અને રામચરિતમાનસ પાઠ સાથે રામ–સીતા વિવાહ ઉજવાય છે.',
                ],
                'rituals' => [
                    'en' => "Worship Shri Ram and Mata Sita together\nRead the Sita–Ram vivah chapter of Ramcharitmanas\nOffer yellow flowers and sweets",
                    'hi' => "श्रीराम और माता सीता की साथ में पूजा करें\nरामचरितमानस का सीता–राम विवाह प्रसंग पढ़ें\nपीले फूल और मिठाई अर्पित करें",
                    'gu' => "શ્રીરામ અને માતા સીતાની સાથે પૂજા કરો\nરામચરિતમાનસનો સીતા–રામ વિવાહ પ્રસંગ વાંચો\nપીળાં ફૂલ અને મીઠાઈ અર્પણ કરો",
                ],
            ],
            [
                'date' => '2026-12-20', 'is_vrat' => true,
                'name' => ['en' => 'Mokshada Ekadashi & Gita Jayanti', 'hi' => 'मोक्षदा एकादशी व गीता जयंती', 'gu' => 'મોક્ષદા એકાદશી અને ગીતા જયંતી'],
                'tithi' => ['en' => 'Margashirsha Shukla Ekadashi', 'hi' => 'मार्गशीर्ष शुक्ल एकादशी', 'gu' => 'માગશર સુદ અગિયારસ'],
                'about' => [
                    'en' => 'On this day Bhagwan Shri Krishna gave the message of the Bhagavad Gita to Arjuna at Kurukshetra. The Ekadashi fast is said to grant moksha.',
                    'hi' => 'इसी दिन भगवान श्रीकृष्ण ने कुरुक्षेत्र में अर्जुन को भगवद्गीता का उपदेश दिया था। यह एकादशी मोक्ष देने वाली मानी जाती है।',
                    'gu' => 'આ દિવસે ભગવાન શ્રીકૃષ્ણે કુરુક્ષેત્રમાં અર્જુનને ભગવદ્ ગીતાનો ઉપદેશ આપ્યો હતો. આ એકાદશી મોક્ષ આપનારી માનવામાં આવે છે.',
                ],
                'rituals' => [
                    'en' => "Keep the Ekadashi fast\nRead or listen to the Bhagavad Gita\nWorship Shri Krishna with tulsi",
                    'hi' => "एकादशी का व्रत रखें\nभगवद्गीता का पाठ करें या सुनें\nतुलसी से श्रीकृष्ण की पूजा करें",
                    'gu' => "એકાદશીનું વ્રત રાખો\nભગવદ્ ગીતાનો પાઠ કરો અથવા સાંભળો\nતુલસીથી શ્રીકૃષ્ણની પૂજા કરો",
                ],
            ],
            [
                'date' => '2026-12-23', 'is_vrat' => false,
                'name' => ['en' => 'Dattatreya Jayanti', 'hi' => 'दत्तात्रेय जयंती', 'gu' => 'દત્તાત્રેય જયંતી'],
                'tithi' => ['en' => 'Margashirsha Purnima', 'hi' => 'मार्गशीर्ष पूर्णिमा', 'gu' => 'માગશર પૂનમ'],
                'about' => [
                    'en' => 'Birth of Bhagwan Dattatreya, the combined form of Brahma, Vishnu and Mahesh, and the first guru. Also the sacred Margashirsha Purnima.',
                    'hi' => 'ब्रह्मा, विष्णु और महेश के संयुक्त रूप तथा आदि गुरु भगवान दत्तात्रेय का जन्मदिवस। साथ ही पवित्र मार्गशीर्ष पूर्णिमा।',
                    'gu' => 'બ્રહ્મા, વિષ્ણુ અને મહેશના સંયુક્ત સ્વરૂપ તથા આદિ ગુરુ ભગવાન દત્તાત્રેયનો જન્મદિવસ. સાથે પવિત્ર માગશર પૂનમ.',
                ],
                'rituals' => $purnimaRituals,
            ],
            [
                'date' => '2027-01-03', 'is_vrat' => true,
                'name' => ['en' => 'Saphala Ekadashi', 'hi' => 'सफला एकादशी', 'gu' => 'સફલા એકાદશી'],
                'tithi' => ['en' => 'Paush Krishna Ekadashi', 'hi' => 'पौष कृष्ण एकादशी', 'gu' => 'માગશર વદ અગિયારસ'],
                'about' => [
                    'en' => 'As its name says, this Ekadashi brings success in work. ' . $ekadashiAbout['en'],
                    'hi' => 'जैसा नाम है, यह एकादशी कार्यों में सफलता देती है। ' . $ekadashiAbout['hi'],
                    'gu' => 'નામ પ્રમાણે, આ એકાદશી કાર્યોમાં સફળતા આપે છે. ' . $ekadashiAbout['gu'],
                ],
                'rituals' => $ekadashiRituals,
            ],
            [
                'date' => '2027-01-14', 'is_vrat' => false,
                'name' => ['en' => 'Uttarayan / Makar Sankranti', 'hi' => 'मकर संक्रांति / उत्तरायण', 'gu' => 'ઉત્તરાયણ / મકરસંક્રાંતિ'],
                'tithi' => ['en' => 'Sun enters Capricorn (Makara)', 'hi' => 'सूर्य का मकर राशि में प्रवेश', 'gu' => 'સૂર્યનો મકર રાશિમાં પ્રવેશ'],
                'about' => [
                    'en' => 'The Sun begins its northward journey. Gujarat fills the sky with kites; people eat til–gud and chikki and give charity. The Sun enters Makara on the night of 14 January, so the punya kaal snan–daan is on 15 January.',
                    'hi' => 'सूर्य उत्तर की ओर यात्रा आरंभ करते हैं। गुजरात में आसमान पतंगों से भर जाता है, तिल–गुड़ और चिक्की खाई जाती है और दान किया जाता है। सूर्य 14 जनवरी की रात मकर में प्रवेश करते हैं, इसलिए स्नान–दान का पुण्य काल 15 जनवरी को है।',
                    'gu' => 'સૂર્ય ઉત્તર તરફની યાત્રા શરૂ કરે છે. ગુજરાતમાં આકાશ પતંગોથી ભરાઈ જાય છે, તલ–ગોળ અને ચીક્કી ખવાય છે અને દાન કરાય છે. સૂર્ય 14 જાન્યુઆરીની રાત્રે મકરમાં પ્રવેશે છે, તેથી સ્નાન–દાનનો પુણ્ય કાળ 15 જાન્યુઆરીએ છે.',
                ],
                'rituals' => [
                    'en' => "Offer water to the Sun at sunrise\nDonate til, jaggery, grain and blankets\nFly kites and share til–gud sweets",
                    'hi' => "सूर्योदय पर सूर्य को अर्घ्य दें\nतिल, गुड़, अन्न और कंबल का दान करें\nपतंग उड़ाएँ और तिल–गुड़ बाँटें",
                    'gu' => "સૂર્યોદયે સૂર્યને અર્ઘ્ય આપો\nતલ, ગોળ, અનાજ અને ધાબળાનું દાન કરો\nપતંગ ચગાવો અને તલ–ગોળ વહેંચો",
                ],
            ],
            [
                'date' => '2027-01-19', 'is_vrat' => true,
                'name' => ['en' => 'Paush Putrada Ekadashi', 'hi' => 'पौष पुत्रदा एकादशी', 'gu' => 'પોષ પુત્રદા એકાદશી'],
                'tithi' => ['en' => 'Paush Shukla Ekadashi', 'hi' => 'पौष शुक्ल एकादशी', 'gu' => 'પોષ સુદ અગિયારસ'],
                'about' => [
                    'en' => 'Observed for the well-being of children and to be blessed with a child. ' . $ekadashiAbout['en'],
                    'hi' => 'संतान के कल्याण और संतान प्राप्ति के लिए रखा जाने वाला व्रत। ' . $ekadashiAbout['hi'],
                    'gu' => 'સંતાનના કલ્યાણ અને સંતાન પ્રાપ્તિ માટે રખાતું વ્રત. ' . $ekadashiAbout['gu'],
                ],
                'rituals' => $ekadashiRituals,
            ],
            [
                'date' => '2027-01-22', 'is_vrat' => false,
                'name' => ['en' => 'Paush Purnima (Shakambhari Purnima)', 'hi' => 'पौष पूर्णिमा (शाकंभरी पूर्णिमा)', 'gu' => 'પોષી પૂનમ (શાકંભરી પૂર્ણિમા)'],
                'tithi' => ['en' => 'Paush Purnima', 'hi' => 'पौष पूर्णिमा', 'gu' => 'પોષ પૂનમ'],
                'about' => [
                    'en' => 'Full moon of Paush, the jayanti of Maa Shakambhari. The holy Magh snan begins from this day.',
                    'hi' => 'पौष मास की पूर्णिमा, माँ शाकंभरी की जयंती। इसी दिन से पवित्र माघ स्नान आरंभ होता है।',
                    'gu' => 'પોષ માસની પૂનમ, મા શાકંભરીની જયંતી. આ દિવસથી પવિત્ર માઘ સ્નાન શરૂ થાય છે.',
                ],
                'rituals' => $purnimaRituals,
            ],
            [
                'date' => '2027-02-02', 'is_vrat' => true,
                'name' => ['en' => 'Shattila Ekadashi', 'hi' => 'षटतिला एकादशी', 'gu' => 'ષટતિલા એકાદશી'],
                'tithi' => ['en' => 'Magh Krishna Ekadashi', 'hi' => 'माघ कृष्ण एकादशी', 'gu' => 'પોષ વદ અગિયારસ'],
                'about' => [
                    'en' => 'Til (sesame) is used in six ways on this day: bath, paste, havan, water, food and charity. ' . $ekadashiAbout['en'],
                    'hi' => 'इस दिन तिल का छह प्रकार से उपयोग होता है: स्नान, उबटन, हवन, जल, भोजन और दान। ' . $ekadashiAbout['hi'],
                    'gu' => 'આ દિવસે તલનો છ રીતે ઉપયોગ થાય છે: સ્નાન, ઉબટન, હવન, જળ, ભોજન અને દાન. ' . $ekadashiAbout['gu'],
                ],
                'rituals' => $ekadashiRituals,
            ],
            [
                'date' => '2027-02-06', 'is_vrat' => false,
                'name' => ['en' => 'Mauni Amavasya', 'hi' => 'मौनी अमावस्या', 'gu' => 'મૌની અમાસ'],
                'tithi' => ['en' => 'Magh Amavasya', 'hi' => 'माघ अमावस्या', 'gu' => 'પોષ અમાસ'],
                'about' => [
                    'en' => 'A day of silence (maun), holy dip and remembrance of ancestors. Bathing in the Ganga or at a sacred river is especially meritorious.',
                    'hi' => 'मौन, पवित्र स्नान और पितरों के स्मरण का दिन। गंगा या किसी पवित्र नदी में स्नान का विशेष पुण्य है।',
                    'gu' => 'મૌન, પવિત્ર સ્નાન અને પિતૃઓના સ્મરણનો દિવસ. ગંગા કે કોઈ પવિત્ર નદીમાં સ્નાનનું વિશેષ પુણ્ય છે.',
                ],
                'rituals' => [
                    'en' => "Keep silence for some time and meditate\nTake a holy bath early in the morning\nOffer tarpan to ancestors and give charity",
                    'hi' => "कुछ समय मौन रखकर ध्यान करें\nप्रातः पवित्र स्नान करें\nपितरों का तर्पण करें और दान दें",
                    'gu' => "થોડો સમય મૌન રાખી ધ્યાન કરો\nસવારે પવિત્ર સ્નાન કરો\nપિતૃઓનું તર્પણ કરો અને દાન આપો",
                ],
            ],
            [
                'date' => '2027-02-10', 'is_vrat' => false,
                'name' => ['en' => 'Ganesh Jayanti', 'hi' => 'गणेश जयंती', 'gu' => 'ગણેશ જયંતી'],
                'tithi' => ['en' => 'Magh Shukla Chaturthi', 'hi' => 'माघ शुक्ल चतुर्थी', 'gu' => 'મહા સુદ ચોથ'],
                'about' => [
                    'en' => 'Birth anniversary of Bhagwan Ganesh, also called Tilkund Chaturthi. Ganesh is worshipped with til laddoos.',
                    'hi' => 'भगवान गणेश का जन्मोत्सव, जिसे तिलकुंद चतुर्थी भी कहते हैं। तिल के लड्डू से गणेश जी की पूजा होती है।',
                    'gu' => 'ભગવાન ગણેશનો જન્મોત્સવ, જેને તિલકુંદ ચતુર્થી પણ કહે છે. તલના લાડુથી ગણેશજીની પૂજા થાય છે.',
                ],
                'rituals' => [
                    'en' => "Worship Bhagwan Ganesh with durva grass\nOffer til laddoos and modak\nChant Om Gan Ganapataye Namah",
                    'hi' => "दूर्वा से भगवान गणेश की पूजा करें\nतिल के लड्डू और मोदक अर्पित करें\nॐ गं गणपतये नमः का जप करें",
                    'gu' => "દૂર્વાથી ભગવાન ગણેશની પૂજા કરો\nતલના લાડુ અને મોદક અર્પણ કરો\nૐ ગં ગણપતયે નમઃ નો જપ કરો",
                ],
            ],
            [
                'date' => '2027-02-11', 'is_vrat' => false,
                'name' => ['en' => 'Vasant Panchami', 'hi' => 'वसंत पंचमी', 'gu' => 'વસંત પંચમી'],
                'tithi' => ['en' => 'Magh Shukla Panchami', 'hi' => 'माघ शुक्ल पंचमी', 'gu' => 'મહા સુદ પાંચમ'],
                'about' => [
                    'en' => 'Welcome of spring and worship of Maa Saraswati, goddess of knowledge and music. A very auspicious day to begin learning.',
                    'hi' => 'वसंत ऋतु का स्वागत और ज्ञान व संगीत की देवी माँ सरस्वती की पूजा। विद्या आरंभ के लिए बहुत शुभ दिन।',
                    'gu' => 'વસંત ઋતુનું સ્વાગત અને જ્ઞાન તથા સંગીતની દેવી મા સરસ્વતીની પૂજા. વિદ્યા આરંભ માટે ખૂબ શુભ દિવસ.',
                ],
                'rituals' => [
                    'en' => "Wear yellow and worship Maa Saraswati\nPlace books and instruments before her\nOffer yellow sweets and flowers",
                    'hi' => "पीले वस्त्र पहनकर माँ सरस्वती की पूजा करें\nपुस्तकें और वाद्य उनके सामने रखें\nपीली मिठाई और फूल अर्पित करें",
                    'gu' => "પીળાં વસ્ત્ર પહેરી મા સરસ્વતીની પૂજા કરો\nપુસ્તકો અને વાદ્ય તેમની સામે મૂકો\nપીળી મીઠાઈ અને ફૂલ અર્પણ કરો",
                ],
            ],
            [
                'date' => '2027-02-13', 'is_vrat' => false,
                'name' => ['en' => 'Ratha Saptami', 'hi' => 'रथ सप्तमी', 'gu' => 'રથ સપ્તમી'],
                'tithi' => ['en' => 'Magh Shukla Saptami', 'hi' => 'माघ शुक्ल सप्तमी', 'gu' => 'મહા સુદ સાતમ'],
                'about' => [
                    'en' => 'Surya Jayanti, the day Surya Dev rides his chariot of seven horses. Worship of the Sun brings health.',
                    'hi' => 'सूर्य जयंती, जिस दिन सूर्य देव सात घोड़ों के रथ पर सवार होते हैं। सूर्य उपासना से आरोग्य मिलता है।',
                    'gu' => 'સૂર્ય જયંતી, જે દિવસે સૂર્ય દેવ સાત ઘોડાના રથ પર સવાર થાય છે. સૂર્ય ઉપાસનાથી આરોગ્ય મળે છે.',
                ],
                'rituals' => [
                    'en' => "Bathe before sunrise\nOffer arghya to Surya Dev\nRecite the Aditya Hridayam",
                    'hi' => "सूर्योदय से पहले स्नान करें\nसूर्य देव को अर्घ्य दें\nआदित्य हृदय स्तोत्र का पाठ करें",
                    'gu' => "સૂર્યોદય પહેલાં સ્નાન કરો\nસૂર્ય દેવને અર્ઘ્ય આપો\nઆદિત્ય હૃદય સ્તોત્રનો પાઠ કરો",
                ],
            ],
            [
                'date' => '2027-02-17', 'is_vrat' => true,
                'name' => ['en' => 'Jaya Ekadashi', 'hi' => 'जया एकादशी', 'gu' => 'જયા એકાદશી'],
                'tithi' => ['en' => 'Magh Shukla Ekadashi', 'hi' => 'माघ शुक्ल एकादशी', 'gu' => 'મહા સુદ અગિયારસ'],
                'about' => [
                    'en' => 'This Ekadashi is said to free one from bad karma and give victory. ' . $ekadashiAbout['en'],
                    'hi' => 'यह एकादशी बुरे कर्मों से मुक्ति और विजय देने वाली मानी जाती है। ' . $ekadashiAbout['hi'],
                    'gu' => 'આ એકાદશી ખરાબ કર્મોથી મુક્તિ અને વિજય આપનારી માનવામાં આવે છે. ' . $ekadashiAbout['gu'],
                ],
                'rituals' => $ekadashiRituals,
            ],
            [
                'date' => '2027-02-20', 'is_vrat' => false,
                'name' => ['en' => 'Magha Purnima', 'hi' => 'माघ पूर्णिमा', 'gu' => 'મહા પૂનમ'],
                'tithi' => ['en' => 'Magh Purnima', 'hi' => 'माघ पूर्णिमा', 'gu' => 'મહા પૂનમ'],
                'about' => [
                    'en' => 'The holy full moon that ends the month of Magh snan. Bathing, charity and Satyanarayan katha are especially meritorious.',
                    'hi' => 'माघ स्नान के मास का समापन करने वाली पवित्र पूर्णिमा। स्नान, दान और सत्यनारायण कथा का विशेष पुण्य है।',
                    'gu' => 'માઘ સ્નાનના માસનું સમાપન કરતી પવિત્ર પૂનમ. સ્નાન, દાન અને સત્યનારાયણ કથાનું વિશેષ પુણ્ય છે.',
                ],
                'rituals' => $purnimaRituals,
            ],
        ];
    }

    public function up(): void
    {
        foreach ($this->festivals() as $f) {
            $exists = Festival::query()->whereDate('date', $f['date'])->get()
                ->contains(fn (Festival $x) => ($x->name['en'] ?? null) === $f['name']['en']);
            if (! $exists) {
                Festival::create($f + ['is_active' => true]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->festivals() as $f) {
            Festival::query()->whereDate('date', $f['date'])->get()
                ->filter(fn (Festival $x) => ($x->name['en'] ?? null) === $f['name']['en'])
                ->each->delete();
        }
    }
};
