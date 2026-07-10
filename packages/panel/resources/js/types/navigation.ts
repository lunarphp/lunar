export interface NavItemShape {
    key: string;
    label: string;
    icon: string | null;
    url: string | null;
    priority: number;
    badge: string | null;
    exact: boolean;
    children: NavItemShape[];
}

export interface NavGroupShape {
    key: string;
    label: string;
    priority: number;
    section: string | null;
    items: NavItemShape[];
}

export interface NavTreeShape {
    groups: NavGroupShape[];
    items: NavItemShape[];
}
