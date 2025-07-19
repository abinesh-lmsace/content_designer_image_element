// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Table of content element actions.
 *
 * @module cdelement_tableofcontents/tableofcontents
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function () {

    const SELECTORS = {
        actionbutton: ".toc-action-block .cta-action-btn",
        tablecontent: ".element-tableofcontents",
        tocblock: ".toc-list-block",
        cdprogressbar: ".contentdesigner-progress",
        tocprogressbar: ".toc-progressbar",
        tocchaptertitle: ".toc-chapter-title",
        tocmodtitle: ".toc-mod-title-block",
        tocblockelem: ".toc-block",
        chaptertitlelist: '.element-tableofcontents .toc-chapter-block .toc-chapter-title',
        contentWrapper: '.contentdesigner-wrapper',
        generaloptions: '.topinstance .item-tableofcontents .general-options',
    };

    const tableofcontent = () => document.querySelector(SELECTORS.tocblock);
    const cdprogressbar = () => document.querySelector(SELECTORS.cdprogressbar);
    const tocprogressbar = () => document.querySelectorAll(SELECTORS.tocprogressbar);
    const tocblock = () => document.querySelector(SELECTORS.tocblockelem);
    const actionbutton = () => document.querySelector(SELECTORS.actionbutton);
    const contentWrapper = () => document.querySelector(SELECTORS.contentWrapper);
    const generaloptionwrapper = () => document.querySelector(SELECTORS.generaloptions);

    /**
     * Initialize the table of content element actions.
     */
    const inittableofcontents = () => {
        // Sticky scroll actions.
        var contentElem = document.querySelector(SELECTORS.tablecontent);

        // Add min height for the parent to avoid height adjustment during the sticky.
        if (tocblock() !== null) {
            var height = tocblock().getBoundingClientRect().height;
            if (height) {
                tocblock().parentNode.style.minHeight = height + 'px';
            }
        }

        if (contentElem && contentElem.classList.contains('stickycontent')) {

            document.querySelector('#page').addEventListener('scroll', () => {
                stickycontents(false);
            });
            window.addEventListener('scroll', () => {
                stickycontents(false);
            });

            // Popup format support.
            var popup = document.querySelector('body.format-popups .modal-content .modal-body');
            if (popup !== null) {
                popup.addEventListener('scroll', () => {
                    stickycontents(true);
                });
            }

            stickycontents(false);
        } else if (contentElem && contentElem.classList.contains('scrollup-stickycontent')) {
            scrollupstickycontents();
        }

        // Listen the event form the element update.
        contentWrapper().addEventListener('elementupdate', () => {
            ctaaction();
        });

        // CTA Actions.
        ctaaction();
    }

    const stickycontents = (popup) => {

        if (popup) {
            var navbar = document.querySelector('body.format-popups .modal-content .modal-header');
        } else {
            var navbar = document.querySelector('nav.navbar.fixed-top');
        }

        var navbarSize = navbar !== null ? navbar.clientHeight : 50;

        if (tocblock() != undefined && tocblock().parentNode.getBoundingClientRect().bottom < navbarSize) {
            displayelementsonsticky();
            if (popup) {
                var rem = (window.innerWidth > 575) ? 1.9 : 0;
                tocblock().style.top = "calc(" + navbarSize + "px + " + rem + "rem)";
            }
        } else {
            hideelementsonsticky();
        }
    }

    const scrollupstickycontents = () => {
        var oldScrollY = window.scrollY;
        window.onscroll = function (e) {
            if (oldScrollY > window.scrollY) {
                stickycontents(false);
            } else {
                hideelementsonsticky();
            }
            oldScrollY = window.scrollY;
        }

        // Handle scrolling inside the popup modal.
        const popup = document.querySelector('body.format-popups .modal-content .modal-body');
        if (popup) {
            let oldPopupScrollY = 0;
            popup.addEventListener('scroll', () => {
                if (popup.scrollTop < oldPopupScrollY) {
                    stickycontents(true); // Scrolling up inside the popup.
                } else {
                    hideelementsonsticky(); // Scrolling down inside the popup.
                }
                oldPopupScrollY = popup.scrollTop;
            });
        }
    }

    const hideelementsonsticky = () => {
        tocblock().classList.remove('fixed-top');
        tableofcontent().classList.remove('header-maxwidth');
        tocprogressbar().forEach((progressbar) => {
            progressbar.classList.add('d-none');
        })
        cdprogressbar().style.display = "block";
        hidechaptertitle(false);
        hidemodtitle(false);
        hideactionbutton(false);
        hidegeneralstyle(false);
    }

    const displayelementsonsticky = () => {
        tocblock().classList.add('fixed-top');
        tableofcontent().classList.add('header-maxwidth');
        tocprogressbar().forEach((progressbar) => {
            progressbar.classList.remove('d-none');
        })
        cdprogressbar().style.display = "none";
        hidechaptertitle(true);
        hidemodtitle(true);
        hideactionbutton(true);
        hidegeneralstyle(true);
    }

    const hidechaptertitle = (display) => {
        var chaptertitle = document.querySelectorAll(SELECTORS.tocchaptertitle);
        chaptertitle.forEach((title) => {
            if (display) {
                if (title.classList.contains('hideonsticky')) {
                    title.style.display = "none";
                }
            } else {
                if (title.classList.contains('hideonsticky')) {
                    title.style.display = "block";
                }
            }
        });
    }

    const hidemodtitle = (display) => {
        var modtitle = document.querySelectorAll(SELECTORS.tocmodtitle);
        modtitle.forEach((title) => {
            if (display) {
                if (title.classList.contains('hideonsticky')) {
                    title.style.display = "none";
                }
            } else {
                if (title.classList.contains('hideonsticky')) {
                    title.style.display = "block";
                }
            }
        });
    }

    const hideactionbutton = (status) => {
        var actionbutton = document.querySelector('.toc-action-block');
        if (actionbutton != undefined) {
            if (status) {
                actionbutton.style.display = "none";
            } else {
                actionbutton.style.display = "block";
            }
        }
    }

    const ctaaction = () => {
        // CTA actions.
        if (actionbutton() != undefined) {
            actionbutton().addEventListener('click', (e) => {
                var chapterid = e.target.dataset.actionid;
                viewtochapter(chapterid);
            });
        }

        // Chapter title linked.
        var chaptertitlelist = document.querySelectorAll(SELECTORS.chaptertitlelist);
        if (chaptertitlelist != undefined) {
            chaptertitlelist.forEach((title) => {
                title.addEventListener('click', (e) => {
                    var chapterid = e.target.dataset.chapter;
                    viewtochapter(chapterid);
                });
            });
        }
    }

    const viewtochapter = (chapterid) => {
        var chapterSelector = document.querySelector('li.chapters-list[data-id="' + chapterid + '"]');
        if (chapterSelector != undefined) {
            chapterSelector.scrollIntoView(true);
        }
    }

    const hidegeneralstyle = (status) => {
        if (generaloptionwrapper() !== undefined) {
            var margin = generaloptionwrapper().dataset.margin;
            var padding = generaloptionwrapper().dataset.padding;
            if (status) {
                generaloptionwrapper().style.margin = "0px";
                generaloptionwrapper().style.padding = "0px";
            } else {
                generaloptionwrapper().style.margin = margin;
                generaloptionwrapper().style.padding = padding;
            }
        }
    }

    return {
        init: function () {
            inittableofcontents();
        },
    };
});
