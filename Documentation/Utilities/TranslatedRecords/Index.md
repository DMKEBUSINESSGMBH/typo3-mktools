Übersetzte Datensätze in Sprachmenüs
===============

In normalen Sprachmenüs prüft TYPO3 auf Detailseiten von Datensätzen (z.B. bei News) nicht, ob es
überhaupt eine übersetzte Version des Datensatzes gibt. Eine Link zu einer Übersetzung
wird also auch angeboten, wenn es gar keine gibt, was sowohl für SEO als auch
für die Nutzer sehr ungünstig ist. Mktools bietet aber eine einfache Möglichkeit,
das zu beheben. Sprachmenüs sind für gewöhnlich wie folgt per TypoScript
aufgebaut:


~~~~ {.sourceCode .ts}
languageMenu = HMENU
languageMenu {
    special = language
    special.value = 0,1
    special.normalWhenNoLanguage = 0
    wrap = <div class="navi navi-lang"><ul>|</ul></div>
    1 = TMENU
    1 {
        noBlur = 1
        NO = 1
        NO {
            doNotLinkIt = 1
            linkWrap = <li>|</li>
            stdWrap.override = DE || EN
            stdWrap {
                typolink {
                    parameter.data = page:uid
                    additionalParams = &L=0 || &L=1
                    ATagParams = hreflang="de-DE" || hreflang="en-GB"
                    title = Deutsch || English
                    addQueryString = 1
                    addQueryString.exclude = L,id,no_cache,cHash
                    addQueryString.method = GET
                    no_cache = 0
                    useCacheHash = 1
                }
            }
        }
        ACT < .NO
        ACT.linkWrap = <li class="active">|</li>
        USERDEF1 < .NO
        USERDEF1 {
            linkWrap = <li><span>|</span></li>
            stdWrap.typolink >
        }
        USERDEF2 < .USERDEF1
    }
}
~~~~

Das TMENU muss einfach um eine itemArrayProcFunc erweitert werden,
in welcher die Parameter der Detailseiten, welche die UID der 
Datensätze enthalten und deren zugehörige Tabelle konfiguriert werden:

~~~~ {.sourceCode .ts}
languageMenu = HMENU
languageMenu {
    special = language
    [...]
    1 = TMENU
    1 {
        [...]
        itemArrayProcFunc = DMK\Mktools\Utility\Menu\Processor\TranslatedRecords->process
        itemArrayProcFunc.parametersConfiguration {
             # GET.PARAMETER.WITH.RECORD.UID = TABLENAME
             tx_news_pi1.news = tx_news_domain_model_news
             tx_myext.record = tx_myext_domain_model_record
             [...]
         }
    }
}
~~~~

Die Funktion prüft dann immer ob einer der Parameter vorhanden ist. Ist das
der Fall, wird auf Vorhandensein einer Übersetzung geprüft, um den Sprachumschalter 
entsprechend auszublenden oder nicht. Die Parameter
werden dabei in der Reihenfolge, wie sie konfiguriert sind, überprüft. Das
ist wichtig zu wissen, falls mehrere Parameter auf einer Seite zum Einsatz kommen.
