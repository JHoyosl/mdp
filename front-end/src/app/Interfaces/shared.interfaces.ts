export interface GenericResponse {
    data: any,
    message: string,
    status: number
}

export interface GenericError {
    message: string,
    errors: any
}