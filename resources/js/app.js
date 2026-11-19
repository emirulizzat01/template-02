import '@fontsource-variable/geist/wght.css';
import '@fontsource-variable/geist-mono/wght.css';
import {
    Activity, ArrowLeft, ArrowUpRight, Boxes, ChartNoAxesCombined, Check, ChevronDown,
    ChevronLeft, ChevronRight, CircleCheck, CircleHelp, ClipboardList, FlaskConical, Gauge, GitBranch, Inbox,
    LayoutDashboard, LogOut, Menu, Minus, Moon, MousePointer2, PanelLeft, RefreshCw, RotateCcw,
    Search, Sun, TriangleAlert, X, createIcons,
} from 'lucide';

import signalShell from './alpine/shell';
import requestChart from './alpine/request-chart';

const icons = {
    Activity, ArrowLeft, ArrowUpRight, Boxes, ChartNoAxesCombined, Check, ChevronDown, ChevronLeft,
    ChevronRight, CircleCheck, CircleHelp, ClipboardList, FlaskConical, Gauge, GitBranch, Inbox, LayoutDashboard,
    LogOut, Menu, Minus, Moon, MousePointer2, PanelLeft, RefreshCw, RotateCcw, Search, Sun, TriangleAlert, X,
};

function renderIcons() {
    createIcons({ icons, attrs: { 'stroke-width': 1.8 } });
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('signalShell', signalShell);
    window.Alpine.data('signalChart', requestChart);
});
document.addEventListener('livewire:init', () => window.Livewire.hook('morphed', renderIcons));
renderIcons();
