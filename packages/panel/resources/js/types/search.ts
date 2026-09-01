export interface SearchResult {
    kind: string;
    kind_label: string;
    icon: string;
    id: string | number;
    label: string;
    hint: string | null;
    url: string;
}

export interface SearchCommand {
    key: string;
    label: string;
    url: string;
    icon: string;
}

export interface SearchKind {
    key: string;
    label: string;
    icon: string;
}
