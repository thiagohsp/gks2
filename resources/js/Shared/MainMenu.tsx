import React from 'react';
import MainMenuItem from './MainMenuItem';

interface MainMenuProps {
    className: string
}

export const MainMenu = ({ className }: MainMenuProps) => {
    return (
        <div className={className}>
            <MainMenuItem text="Dashboard" link="dashboard" icon="dashboard" />
            <MainMenuItem text="Lotes" link="lotes" icon="printer" />
        </div>
    );
};

export default MainMenu;
