import styled from 'styled-components';

export const Container = styled.div`

    label {
        display: block;
        margin-bottom: 5px;
    }

    input {
        width: 100%;
        padding: 8px 16px;
        border-radius: 4px;
        border: 2px solid #ddd;
        font-size: 15px;
        color: #444;
        transition: border-color 0.2s;

        &:focus {
            outline: none;
            box-shadow: 0px 0px 4px #5a67d8;
        }
    }
`;
