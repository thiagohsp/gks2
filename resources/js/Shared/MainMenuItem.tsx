// import React from 'react';
import { InertiaLink } from '@inertiajs/inertia-react';
import classNames from 'classnames';
import Icon from '../Shared/Icon';
import React from 'react';
import route from 'ziggy-js';

// import { Container } from './styles';

interface MenuItemProps {
    icon: string,
    link: string,
    text: string
}

export const MainMenuItem = ({ icon, link, text }: MenuItemProps) => {

    const isActive = route().current(link + '*');

    const iconClasses = classNames('w-4 h-4 mr-2', {
        'text-white fill-current': isActive,
        'text-indigo-400 group-hover:text-white fill-current': !isActive
    });

    const textClasses = classNames({
        'text-white': isActive,
        'text-indigo-200 group-hover:text-white': !isActive
    });

    return (

        <div className="mb-4">
            <InertiaLink href={route(link)} className="flex items-center group py-3">
                <Icon name={icon} className={iconClasses} />
                <div className={textClasses}>{text}</div>
            </InertiaLink>
        </div>

    );
}

export default MainMenuItem;
