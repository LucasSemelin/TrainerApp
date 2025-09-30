export interface Profile {
    id: string;
    user_id: string;
    first_name: string;
    last_name: string;
    gender?: string | null;
}

export interface Client {
    id: string;
    email: string;
    profile?: Profile;
}
